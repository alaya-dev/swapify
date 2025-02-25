<?php

namespace App\Service;
use App\Entity\User;
use App\Entity\Event;
use App\Repository\AttendanceRepository;
use App\Repository\ParticipantEventRepository;
use App\Repository\SessionRepository;


class ParticipationCalculatorService
{
    private $attendanceRepository;
    private $sessionRepository;
    private $participantEventRepository;

    public function __construct(
        AttendanceRepository $attendanceRepository,
        SessionRepository $sessionRepository,
        ParticipantEventRepository $participantEventRepository
    ) {
        $this->attendanceRepository = $attendanceRepository;
        $this->sessionRepository = $sessionRepository;
        $this->participantEventRepository = $participantEventRepository;
    }

    public function calculateParticipationPercentage(User $user, Event $event): array
    {
        // Find participant record for this user and event
        $participantEvent = $this->participantEventRepository->findOneBy([
            'user' => $user,
            'event' => $event
        ]);



        // Get all sessions for this event
        $allSessions = $this->sessionRepository->findBy(['event' => $event]);
        $totalSessions = count($allSessions);
        // Get all attendances for this participant
        $attendances = $this->attendanceRepository->findBy([
            'participantEvent' => $participantEvent,
            'attended' => true
        ]);
        
        $sessionsAttended = count($attendances);
        $percentage = ($sessionsAttended / $totalSessions) * 100;
        $isCertificateEligible = $percentage >= 75;

        return [
            'percentage' => round($percentage, 2),
            'sessionsAttended' => $sessionsAttended,
            'totalSessions' => $totalSessions,
            'isCertificateEligible' => $isCertificateEligible,
            'message' => $isCertificateEligible 
                ? 'User is eligible for certificate' 
                : 'User needs to attend more sessions to be eligible for certificate'
        ];
    }
}