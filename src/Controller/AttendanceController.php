<?php

namespace App\Controller;

use App\Entity\Attendance;
use App\Entity\Session;
use App\Repository\AttendanceRepository;
use App\Service\QrCodeService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AttendanceController extends AbstractController{

#[Route('/validate_attendance/{id}', name: 'validate_attendance', methods: ['POST'])]
public function verifyAttendanceCode(int $id, Request $request,EntityManagerInterface $em,AttendanceRepository $attendanceRepo,Attendance $attendance)
{
     $user = $this->getUser();
     $data = json_decode($request->getContent(), true);
    
     $code = $data['code'] ?? '';
     

     if ($attendance && $attendance->getCode() == $code) {
         $attendance->setAttended(true);
        $em->flush();
         return new JsonResponse(['success' => true]);
     }

    return new JsonResponse(['success' => false], 400);
}

    

}
