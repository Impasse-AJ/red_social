<?php

namespace App\Controller;

use App\Entity\Amistad;
use App\Entity\Usuario;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class AmistadController extends AbstractController
{
    #[Route('/amistad/enviar/{id}', name: 'enviar_solicitud', methods: ['POST'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function enviarSolicitud(int $id, EntityManagerInterface $entityManager): Response
    {
        $solicitante = $this->getUser();
        $receptor = $entityManager->getRepository(Usuario::class)->find($id);

        if (!$receptor || $solicitante->getId() === $receptor->getId()) {
            return $this->redirectToRoute('ctrl_home');
        }

        // Verificar si ya existe una solicitud o son amigos
        $amistadExistente = $entityManager->getRepository(Amistad::class)->findOneBy([
            'solicitante' => $solicitante,
            'receptor' => $receptor
        ]);

        if (!$amistadExistente) {
            $amistad = new Amistad();
            $amistad->setSolicitante($solicitante);
            $amistad->setReceptor($receptor);
            $amistad->setEstado('pendiente');

            $entityManager->persist($amistad);
            $entityManager->flush();
        }

        return $this->redirectToRoute('ver_perfil', ['id' => $id]);
    }


    #[Route('/amistades/responder/{id}', name: 'responder_solicitud', methods: ['POST'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function responderSolicitud(int $id, Request $request, EntityManagerInterface $entityManager): Response
    {
        $usuario = $this->getUser();
        $solicitud = $entityManager->getRepository(Amistad::class)->find($id);

        if (!$solicitud || $solicitud->getReceptor()->getId() !== $usuario->getId()) {
            throw $this->createAccessDeniedException("No tienes permiso para responder esta solicitud.");
        }

        $accion = $request->request->get('accion');

        if ($accion === 'aceptar') {
            $solicitud->setEstado('aceptada');
        } elseif ($accion === 'rechazar') {
            $solicitud->setEstado('rechazada');
            $entityManager->remove($solicitud);
        }

        $entityManager->flush();

        return $this->redirectToRoute('ver_solicitudes');
    }

    #[Route('/amistad/eliminar/{id}', name: 'eliminar_amistad', methods: ['POST'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function eliminarAmistad(int $id, EntityManagerInterface $entityManager): Response
    {
        $usuario = $this->getUser();
        $amigo = $entityManager->getRepository(Usuario::class)->find($id);
    
        if (!$amigo) {
            return $this->redirectToRoute('ctrl_home');
        }
    
        // Buscar la amistad donde el usuario es el solicitante y el amigo es el receptor
        $amistad = $entityManager->getRepository(Amistad::class)->findOneBy([
            'solicitante' => $usuario,
            'receptor' => $amigo,
            'estado' => 'aceptada'
        ]);
    
        // Si no se encontró, buscar donde el usuario es el receptor y el amigo es el solicitante
        if (!$amistad) {
            $amistad = $entityManager->getRepository(Amistad::class)->findOneBy([
                'solicitante' => $amigo,
                'receptor' => $usuario,
                'estado' => 'aceptada'
            ]);
        }
    
        // Si existe la amistad, eliminarla
        if ($amistad) {
            $entityManager->remove($amistad);
            $entityManager->flush();
        }
    
        return $this->redirectToRoute('ver_perfil', ['id' => $id]);
    }
    

    #[Route('/amistades/solicitudes', name: 'ver_solicitudes')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')] // Solo usuarios autenticados pueden ver solicitudes
    public function verSolicitudes(EntityManagerInterface $entityManager): Response
    {
        $usuario = $this->getUser(); // Obtener el usuario autenticado

        // Obtener todas las solicitudes pendientes dirigidas a este usuario
        $solicitudes = $entityManager->getRepository(Amistad::class)->findBy([
            'receptor' => $usuario,
            'estado' => 'pendiente'
        ]);

        return $this->render('solicitudes.html.twig', [
            'solicitudes' => $solicitudes
        ]);
    }
}
