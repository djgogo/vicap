<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Repository;

use App\Entity\Media;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Media>
 *
 * @method Media|null find($id, $lockMode = null, $lockVersion = null)
 * @method Media|null findOneBy(array $criteria, array $orderBy = null)
 * @method Media[]    findAll()
 * @method Media[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MediaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Media::class);
    }

//    /**  TODO -> add User relation to the media entity
//     * @return Media[] Returns an array of CandidateFile objects
//     */
//    public function getAllFilesByUser(User $user): array
//    {
//        return $this->createQueryBuilder('cf')
//            ->andWhere('cf.candidate = :user')
//            ->setParameter('user', $user)
//            ->orderBy('cf.created', 'DESC')
//            ->getQuery()
//            ->getResult();
//    }

}
