<?php

namespace App\Controller;

use App\Entity\Balance;
use App\Entity\Transactions;
use App\Repository\BalanceRepository;
use App\Repository\CompanyRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class BalanceController extends AbstractController
{
    private $variables;

    public function __construct()
    {
        // Загрузка файла lang.php
        $this->variables = include __DIR__.'/../../Static/Variables.php';
    }

    #[Route('/balances', name: 'balances_index', methods: ['GET'])]
    /**
     * Получение списка балансов.
     */
    public function index(BalanceRepository $balanceRepository): Response
    {
        $balances = $balanceRepository->findAll();

        $data = [];
        foreach ($balances as $balanc) {
            $data[] = [
                'id' => $balanc->getId(),
                'phone' => $balanc->getPhone(),
                'company' => $balanc->getCompany()->getName(),
                'balance' => $balanc->getBalance(),
            ];
        }

        return new JsonResponse($data);
    }

    #[Route('/balances/transaction', name: 'balance_transaction', methods: ['GET'])]
    /**
     * Просмотр транзакций по балансу.
     */
    public function getBalanceTransaction(Request $request,
        BalanceRepository $balanceRepository,
        CompanyRepository $companyRepository): Response
    {
        $request = json_decode($request->getContent(), true);

        $balanc = $balanceRepository->findOneBy(['phone' => $request['phone'],
            'company' => $companyRepository->find($request['companyId'])]);
        if (!$balanc) {
            $data = [
                'status' => 404,
                'errors' => $this->variables['error_balance_not_found'],
            ];

            return new JsonResponse($data, 404);
        }
        $transactions = $balanc->getTransactions();
        $data = [];
        foreach ($transactions as $transaction) {
            $data[] = [
                'id' => $transaction->getId(),
                'balance_id' => $transaction->getBalance()->getId(),
                'timestamp' => $transaction->getTimestamp()->format('d-m-Y-H-i-s'),
                'changes' => $transaction->getChanges(),
                'type_change' => $transaction->getTypeChange(),
            ];
        }

        return new JsonResponse($data);
    }

    #[Route('/balances/{phone}', name: 'balances_show', methods: ['GET'])]
    /**
     * Просмотр существуюих балансов по номеру телефона.
     */
    public function show(BalanceRepository $balanceRepository, $phone): Response
    {
        $balancs = $balanceRepository->findBy(['phone' => $phone]);
        if (!$balancs) {
            $data = [
                'status' => 404,
                'errors' => $this->variables['error_balance_not_found'],
            ];

            return new JsonResponse($data, 404);
        }
        foreach ($balancs as $balanc) {
            $data[] = [
                'id' => $balanc->getId(),
                'phone' => $balanc->getPhone(),
                'company' => $balanc->getCompany()->getName(),
                'balance' => $balanc->getBalance(),
            ];
        }

        return new JsonResponse($data);
    }

    #[Route('/balances/create', name: 'balance_create', methods: ['POST'])]
    /**
     * Создание баланса.
     */
    public function create(Request $request, EntityManagerInterface $em,
        ValidatorInterface $validator,
        BalanceRepository $balanceRepository,
        CompanyRepository $companyRepository): Response
    {
        $request = json_decode($request->getContent(), true);

        if ($balanceRepository->findOneBy(['phone' => $request['phone'],
            'company' => $companyRepository->find($request['companyId'])])) {
            $data = [
                'status' => 409,
                'errors' => $this->variables['error_balance_already_exists'],
            ];

            return new JsonResponse($data, 409);
        }
        $company = $companyRepository->find($request['companyId']);

        if ($request['balance'] < 0) {
            $data = [
                'status' => 422,
                'errors' => $this->variables['error_balance_negative'],
            ];

            return new JsonResponse($data, 422);
        }

        $balanc = new Balance();
        $balanc->setPhone($request['phone'])
            ->setCompany($company)
            ->setBalance($request['balance']);

        $error = $validator->validate($balanc);

        if (count($error) > 0) {
            $data = [
                'status' => 400,
                'errors' => $this->variables['error_request'],
            ];

            return new JsonResponse($data, 400);
        }

        $transaction = new Transactions();
        $transaction->setBalance($balanc)
            ->setTimestamp(new \DateTime())
            ->setChanges($request['balance'])
            ->setTypeChange('Создание баланса');

        $balanc->addTransaction($transaction);

        $em->persist($balanc);
        $em->persist($transaction);
        $em->flush();

        $data = [
            'status' => 200,
            'success' => $this->variables['success_balance_created'],
        ];

        return new JsonResponse($data, 200);
    }

    #[Route('/balances/modification', name: 'balance_modification', methods: ['POST'])]
    /**
     * Начисление/Списание с баланса по номеру телефона.
     */
    public function modification(Request $request, EntityManagerInterface $em,
        ValidatorInterface $validator,
        BalanceRepository $balanceRepository,
        CompanyRepository $companyRepository): Response
    {
        $request = json_decode($request->getContent(), true);

        $balanc = $balanceRepository->findOneBy(['phone' => $request['phone'],
            'company' => $companyRepository->find($request['companyId'])]);
        if (!$balanc) {
            $data = [
                'status' => 404,
                'errors' => $this->variables['error_balance_not_found'],
            ];

            return new JsonResponse($data, 404);
        }

        $currentBalance = $balanc->getBalance() + $request['amount'];
        if ($currentBalance < 0) {
            $data = [
                'status' => 422,
                'errors' => $this->variables['error_balance_negative'],
            ];

            return new JsonResponse($data, 420);
        }
        $balanc->setBalance($currentBalance);

        $error = $validator->validate($balanc);

        if (count($error) > 0) {
            $data = [
                'status' => 400,
                'errors' => $this->variables['error_request'],
            ];

            return new JsonResponse($data, 400);
        }

        $transaction = new Transactions();
        if ($request['amount'] >= 0) {
            $transaction->setBalance($balanc)
                ->setTimestamp(new \DateTime())
                ->setChanges($request['amount'])
                ->setTypeChange('Пополнение баланса на '.$request['amount'] / 100 .' рублей');
        } else {
            $transaction->setBalance($balanc)
                ->setTimestamp(new \DateTime())
                ->setChanges($request['amount'])
                ->setTypeChange('Списание баланса на '.$request['amount'] / 100 .' рублей');
        }

        $balanc->addTransaction($transaction);

        $em->persist($balanc);
        $em->persist($transaction);
        $em->flush();

        $data = [
            'status' => 200,
            'success' => $this->variables['success_balance_modified'],
        ];

        return new JsonResponse($data, 242);
    }

    #[Route('/balances/send', name: 'balance_send', methods: ['POST'])]
    /**
     * Перевод средств от пользователя к пользователю.
     */
    public function send(Request $request, EntityManagerInterface $em,
        ValidatorInterface $validator,
        BalanceRepository $balanceRepository,
        CompanyRepository $companyRepository): Response
    {
        $request = json_decode($request->getContent(), true);

        $balanc1 = $balanceRepository->findOneBy(['phone' => $request['phone1'],
            'company' => $companyRepository->find($request['companyId1'])]);
        if (!$balanc1) {
            $data = [
                'status' => 404,
                'errors' => $this->variables['error_sender_balance_not_exists'],
            ];

            return new JsonResponse($data, 404);
        }
        $balanc2 = $balanceRepository->findOneBy(['phone' => $request['phone2'],
            'company' => $companyRepository->find($request['companyId2'])]);
        if (!$balanc2) {
            $data = [
                'status' => 404,
                'errors' => $this->variables['error_recipient_balance_not_exists'],
            ];

            return new JsonResponse($data, 404);
        }
        if ($request['amount'] < 0) {
            $data = [
                'status' => 422,
                'errors' => $this->variables['error_negative_amount'],
            ];

            return new JsonResponse($data, 422);
        }
        if ($balanc1->getBalance() - $request['amount'] < 0) {
            $data = [
                'status' => 422,
                'errors' => $this->variables['error_negative_balance_after_change'],
            ];

            return new JsonResponse($data, 422);
        }
        $balanc1->setBalance($balanc1->getBalance() - $request['amount']);
        $balanc2->setBalance($balanc2->getBalance() + $request['amount']);

        $error = $validator->validate($balanc1);

        if (count($error) > 0) {
            $data = [
                'status' => 400,
                'errors' => $this->variables['error_request'],
            ];

            return new JsonResponse($data, 400);
        }
        $error = $validator->validate($balanc2);

        if (count($error) > 0) {
            $data = [
                'status' => 400,
                'errors' => $this->variables['error_request'],
            ];

            return new JsonResponse($data, 400);
        }

        $transaction1 = new Transactions();
        $transaction1->setBalance($balanc1)
            ->setTimestamp(new \DateTime())
            ->setChanges(-$request['amount'])
            ->setTypeChange('Отправка с баланса на счет '
                .$request['phone2'].' в компанию '
                .$balanc2->getCompany()->getName().' на сумму '.$request['amount'] / 100 .' рублей');

        $transaction2 = new Transactions();
        $transaction2->setBalance($balanc2)
            ->setTimestamp(new \DateTime())
            ->setChanges($request['amount'])
            ->setTypeChange('Получение на баланс со счета '
                .$request['phone1'].' из компании '
                .$balanc1->getCompany()->getName().' на сумму '.$request['amount'] / 100 .' рублей');

        $balanc1->addTransaction($transaction1);
        $balanc2->addTransaction($transaction2);

        $em->persist($balanc1);
        $em->persist($balanc2);
        $em->persist($transaction1);
        $em->persist($transaction2);
        $em->flush();

        $data = [
            'status' => 200,
            'success' => $this->variables['success_amount_sent'],
        ];

        return new JsonResponse($data, 200);
    }
}
