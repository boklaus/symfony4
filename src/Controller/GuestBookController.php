<?php

namespace App\Controller;

use App\Entity\Comment;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class GuestBookController extends AbstractController
{
    public function home(Request $request): Response
    {
        $repos = $this->getDoctrine()->getRepository(Comment::class);
        $comments = $repos->findAll();

        $form = $this->commentRequest($request);

        if($form instanceof RedirectResponse) {
            $comment = new Comment();
            $comment->setName('');
            $comment->setEmail('');
            $comment->setContent('');

            $form = $this->createFormBuilder($comment)
                ->add('name', TextType::class)
                ->add('email', EmailType::class)
                ->add('content', TextareaType::class)
                ->add('save', SubmitType::class, array('label' => 'Add comment'))
                ->getForm();
        }

        return $this->render('home.html.twig', [
            'comments' => $comments,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @param Request $request
     *
     * @return \Symfony\Component\Form\FormInterface|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    private function commentRequest(Request $request)
    {
        $comment = new Comment();
        $comment->setName('');
        $comment->setEmail('');
        $comment->setContent('');

        $form = $this->createFormBuilder($comment)
            ->add('name', TextType::class)
            ->add('email', EmailType::class)
            ->add('content', TextareaType::class)
            ->add('save', SubmitType::class, array('label' => 'Add comment'))
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $comment = $form->getData();
            $comment->setCreatedAt(new \DateTime());

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($comment);
            $entityManager->flush();

            return $this->redirect('/');
        }

        return $form;
    }
}
