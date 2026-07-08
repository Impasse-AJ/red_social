<?php

namespace App\Controller;

use App\Entity\Amistad;
use App\Entity\Usuario;
use App\Enum\EstadoAmistad;
use App\Repository\AmistadRepository;
use App\Repository\UsuarioRepository;
use App\Security\Voter\AmistadVoter;
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
    public function enviarSolicitud(
        int $id,
        Request $request,
        UsuarioRepository $usuarios,
        AmistadRepository $amistades,
        EntityManagerInterface $entityManager
    ): Response {
        if (!$this->isCsrfTokenValid('amistad', (string) $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Token CSRF inválido.');
        }

        /** @var Usuario $solicitante */
        $solicitante = $this->getUser();
        $receptor = $usuarios->find($id);

        if (!$receptor || $solicitante->getId() === $receptor->getId()) {
            return $this->redirectToRoute('ctrl_home');
        }

        // Solo se crea si no existe ya una solicitud o amistad entre ambos
        $amistadExistente = $amistades->findOneBy([
            'solicitante' => $solicitante,
            'receptor' => $receptor,
        ]);

        if (!$amistadExistente) {
            $amistad = new Amistad();
            $amistad->setSolicitante($solicitante);
            $amistad->setReceptor($receptor);
            $amistad->setEstado(EstadoAmistad::Pendiente);

            $entityManager->persist($amistad);
            $entityManager->flush();
        }

        return $this->redirectToRoute('ver_perfil', ['id' => $id]);
    }

    #[Route('/amistades/responder/{id}', name: 'responder_solicitud', methods: ['POST'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function responderSolicitud(
        int $id,
        Request $request,
        AmistadRepository $amistades,
        EntityManagerInterface $entityManager
    ): Response {
        if (!$this->isCsrfTokenValid('responder_solicitud', (string) $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Token CSRF inválido.');
        }

        $solicitud = $amistades->find($id);

        if (!$solicitud) {
            throw $this->createNotFoundException('La solicitud no existe.');
        }

        $this->denyAccessUnlessGranted(AmistadVoter::RESPONDER, $solicitud, 'No tienes permiso para responder esta solicitud.');

        $accion = $request->request->get('accion');

        if ($accion === 'aceptar') {
            $solicitud->setEstado(EstadoAmistad::Aceptada);
        } elseif ($accion === 'rechazar') {
            $entityManager->remove($solicitud);
        }

        $entityManager->flush();

        return $this->redirectToRoute('ver_solicitudes');
    }

    #[Route('/amistad/eliminar/{id}', name: 'eliminar_amistad', methods: ['POST'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function eliminarAmistad(
        int $id,
        Request $request,
        UsuarioRepository $usuarios,
        AmistadRepository $amistades,
        EntityManagerInterface $entityManager
    ): Response {
        if (!$this->isCsrfTokenValid('amistad', (string) $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Token CSRF inválido.');
        }

        /** @var Usuario $usuario */
        $usuario = $this->getUser();
        $amigo = $usuarios->find($id);

        if (!$amigo) {
            return $this->redirectToRoute('ctrl_home');
        }

        $amistad = $amistades->amistadAceptadaEntre($usuario, $amigo);

        if ($amistad) {
            $entityManager->remove($amistad);
            $entityManager->flush();
        }

        return $this->redirectToRoute('ver_perfil', ['id' => $id]);
    }

    #[Route('/amistades/solicitudes', name: 'ver_solicitudes')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function verSolicitudes(AmistadRepository $amistades): Response
    {
        /** @var Usuario $usuario */
        $usuario = $this->getUser();

        return $this->render('solicitudes.html.twig', [
            'solicitudes' => $amistades->solicitudesPendientesDe($usuario),
        ]);
    }
}
