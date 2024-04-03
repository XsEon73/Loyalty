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
    private mixed $variables;

    public function __construct()
    {
        $this->variables = include __DIR__.'/../../Static/Variables.php';
    }

    #[Route('/companies', name: 'company_index', methods: ['GET'])]
    /**
     * Просмотр существующих компаний.
     */
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

    #[Route('/companies/balances/{id}', name: 'company_balance', methods: ['GET'])]
    /**
     * Просмотр существующих балансов, которые привязаны к компании.
     */
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
    /**
     * Просмотр одной компании.
     */
    public function show(CompanyRepository $companyRepository, $id): Response
    {
        $company = $companyRepository->find($id);
        if (!$company) {
            $data = [
                'status' => 404,
                'errors' => $this->variables['error_company_not_found'],
            ];

            return new JsonResponse($data, 404);
        }

        $data = [
            'id' => $company->getId(),
            'name' => $company->getName(),
        ];

        return new JsonResponse($data);
    }

    #[Route('/companies/create', name: 'company_create', methods: ['POST'])]
    /**
     * Создание компании.
     */
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
                'success' => $this->variables['success_company_created'],
            ];

            return new JsonResponse($data, 200);
        } catch (\Exception $e) {
            $data = [
                'status' => 400,
                'errors' => $this->variables['error_request'],
            ];

            return new JsonResponse($data, 422);
        }
    }
}
