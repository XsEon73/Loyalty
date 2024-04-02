<?php

namespace App\Controller;

use App\Entity\Company;
use App\Repository\CompanyRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CompanyController extends AbstractController
{
    #[Route('/companies', name: 'company_index', methods: ['GET'])]
    public function index(CompanyRepository $companyRepository): Response
    {
        $companies = $companyRepository->findAll();

        $data = [];
        foreach ($companies as $company) {
            $data[] = [
                'id' => $company->getId(),
                'name' => $company->getName(),
            ];
        }

        return new JsonResponse($data);
    }

    #[Route('/companies/{id}/balances', name: 'company_balance', methods: ['GET'])]
    public function getCompanyBalance(Company $company): Response
    {
        $balances = $company->getBalances();
        $data = [];
        foreach ($balances as $balance) {
            $data[] = [
                'id' => $balance->getId(),
                'phone' => $balance->getPhone(),
                'balance' => $balance->getBalance(),
            ];
        }

        return new JsonResponse($data);
    }

    #[Route('/companies/{id}', name: 'company_show', methods: ['GET'])]
    public function show(CompanyRepository $companyRepository, $id): Response
    {
        $company = $companyRepository->find($id);
        if (!$company) {
            $data = [
                'status' => 404,
                'errors' => 'Компания не найдена',
            ];

            return new JsonResponse($data, 404);
        }

        $data = [
            'id' => $company->getId(),
            'name' => $company->getName(),
        ];

        return new JsonResponse($data);
    }

    #[Route('/companies/', name: 'company_create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $em): Response
    {
        try {
            $request = json_decode($request->getContent(), true);
            $company = new Company();
            $company->setName($request['name']);

            $em->persist($company);
            $em->flush();

            $data = [
                'status' => 200,
                'success' => 'Компания успешно создана',
            ];

            return new JsonResponse($data, 200);
        } catch (\Exception $e) {
            $data = [
                'status' => 422,
                'errors' => 'Недостоверные данные',
            ];

            return new JsonResponse($data, 422);
        }
    }
}
