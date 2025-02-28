<?php
namespace App\Service;


use Symfony\Contracts\HttpClient\HttpClientInterface;

class RecommandationService {
    private $client;

    public function __construct(HttpClientInterface $client) {
        $this->client = $client;
    }

 
public function getRecommendations($annonceId) {
    try {
        $response = $this->client->request('GET', 'http://127.0.0.1:5000/recommend/' . $annonceId);
        return $response->toArray();
    } catch (\Exception $e) {
        // Log l'erreur ou retournez un tableau vide
        error_log("Erreur lors de la récupération des recommandations : " . $e->getMessage());
        return [];
    }
}
}

