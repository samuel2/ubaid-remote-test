<?php

namespace App\Controller;

use App\Entity\Organization;
use App\Form\OrganizationType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Yaml\Yaml;

/**
 * @Route("/organization")
 */
class OrganizationController extends AbstractController
{
    /**
     * @Route("/", name="organization_index", methods={"GET"})
     */
    public function index(): Response
    {
        $dataSource = Yaml::parseFile('organizations.yaml');
        //var_dump($dataSource['organizations']);
        return $this->render('organization/index.html.twig', [
            'organizations' => $dataSource['organizations'],
        ]);
    }

    /**
     * @Route("/new", name="organization_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $olds = Yaml::parseFile('organizations.yaml');
        $organization = new Organization();
        $form = $this->createForm(OrganizationType::class, $organization);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $val = (int)$request->get('user_length');
            $users = [];
            for ($i = 1; $i <= $val; $i++) {
                $name = 'user_name'.$i;
                $role = 'user_role'.$i;
                $password = 'user_password'.$i;

                $tmp_roles = explode(",", $request->get($role));
                $roles = [];
                for ($j = 0; $j < sizeof($tmp_roles); $j++) {
                    array_push($roles, $tmp_roles[$j]);
                }

                $user = [
                    'name' => $request->get($name),
                    'role' => $roles,
                    'password' => $request->get($password)
                ];
                array_push($users, $user);
            }

            $org = [
                'name' => $organization->getName(),
                'description' => $organization->getDescription(),
                'users' => $users
            ];

            array_push($olds['organizations'], $org);
            $dumped = Yaml::dump($olds);
            file_put_contents('organizations.yaml', $dumped);
            return $this->redirectToRoute('organization_index');
        }

        return $this->render('organization/new.html.twig', [
            'organization' => $organization,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="organization_show", methods={"GET"})
     */
    public function show($id): Response
    {
        $data = Yaml::parseFile('organizations.yaml');
        return $this->render('organization/show.html.twig', [
            'organization' => $data['organizations'][$id-1],
        ]);
    }

    /**
     * @Route("/{id}/edit", name="organization_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, $id): Response
    {
        $data = Yaml::parseFile('organizations.yaml');
        $organization = new Organization();
        $organization->setName($data['organizations'][$id-1]['name']);
        $organization->setDescription($data['organizations'][$id-1]['description']);

        $current_data = $data['organizations'][$id-1]['users'];
        $users = [];
        for ($i = 0; $i < sizeof($current_data); $i++) {

            $roles = '';
            for ($j = 0; $j < sizeof($current_data[$i]['role']); $j++) {
                $roles = $roles . $current_data[$i]['role'][$j].', ';
            }

            $user = [
                'name' => $current_data[$i]['name'],
                'role' => $roles,
                'password' => $current_data[$i]['password']
            ];
            array_push($users, $user);
        }

        $form = $this->createForm(OrganizationType::class, $organization);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $val = (int)$request->get('user_length');
            $form_data = $request->get('organization');

            $usersToUpdate = [];
            for ($i = 1; $i <= $val; $i++) {
                $name = 'user_name'.$i;
                $role = 'user_role'.$i;
                $password = 'user_password'.$i;

                $tmp_roles = explode(",", $request->get($role));
                $roles = [];
                for ($j = 0; $j < sizeof($tmp_roles); $j++) {
                    array_push($roles, $tmp_roles[$j]);
                }

                $user = [
                    'name' => $request->get($name),
                    'role' => $roles,
                    'password' => $request->get($password)
                ];
                array_push($usersToUpdate, $user);
            }

            $org = [
                'name' => $organization->getName($form_data['name']),
                'description' => $organization->getDescription($form_data['description']),
                'users' => $usersToUpdate
            ];

            $cmpt = 0;
            $newData['organizations'] = [];
            foreach ($data['organizations'] as $organization) {
                if ($cmpt === $id-1) {
                    array_push($newData['organizations'], $org);
                } else {
                    array_push($newData['organizations'], $organization);
                }
                $cmpt = $cmpt + 1;
            }
            $dumped = Yaml::dump($newData);
            file_put_contents('organizations.yaml', $dumped);
            return $this->redirectToRoute('organization_index');
        }

        return $this->render('organization/edit.html.twig', [
            'organization' => $organization,
            'users' => $users,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="organization_delete", methods={"DELETE"})
     */
    public function delete($id): Response
    {
        $data = Yaml::parseFile('organizations.yaml');
        var_dump($data['organizations'][$id-1]);
        $cmpt = 0;
        $newData['organizations'] = [];
        foreach ($data['organizations'] as $organization) {
            if ($cmpt !== $id-1) {
                array_push($newData['organizations'], $organization);
            }
            $cmpt = $cmpt + 1;
        }
        $dumped = Yaml::dump($newData);
        file_put_contents('organizations.yaml', $dumped);
        return $this->redirectToRoute('organization_index');
    }
}
