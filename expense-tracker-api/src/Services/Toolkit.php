<?php
namespace App\Service;

use App\Entity\User;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;


/*
**
**  Class Toolkit
**  Cette classe contient des fonctions utiles pour travailler avec les données utilisateur et les entités.
**  pour ne pas surcharger les code de l'application et les controllers
**  @author BABA Aristote Cleven <babaaristote6m@gmail.com>
*/

class Toolkit 
{  
    private EntityManagerInterface $entityManager;
    private SerializerInterface $serializer;
    public function __construct(EntityManagerInterface $entityManager, SerializerInterface $serializer)
    {
        $this->entityManager = $entityManager;  
        $this->serializer = $serializer;
    }
    /**
     * @param array $dataSelect
     * @return array
     * 
     * Renvoie un tableau de noms d'entité avec la première lettre en majuscule
     * conçu pour intervenir au sein de la fonction qui se charge de retourner les select
     * 
     * @author BABA Aristote Cleven <babaaristote6m@gmail.com>
     */
    public function formatArrayEntity(array $dataSelect): array
    {
        return array_map(function ($value) {
            // Mettre la première lettre en majuscule
            $value = ucfirst($value);
    
            // Retirer le 's' final s'il y en a
            if (str_ends_with($value, 's')) {
                $value = substr($value, 0, -1);
            }
    
            return $value;
        }, $dataSelect);
    }

    /**
     * @param array $dataSelect
     * @return array
     * 
     * Renvoie un tableau pour peupler les select de l'application avec les ID et les labels ou descriptions de chaque entité
     * @author BABA Aristote Cleven <babaaristote6m@gmail.com>
     */
    public function formatArrayEntityLabel(array $dataSelect): array
    {
        $allData = [];
        foreach ($dataSelect as $key => $value) {
            $entities = $this->entityManager->getRepository('App\Entity\\'.$value)->findAll();
            $data = json_decode($this->serializer->serialize($entities, 'json', ['groups' => 'data_select']),true);
            $allData[strtolower($value)] = $data;
        }

        // dd($allData);
        return $allData;
    }

}