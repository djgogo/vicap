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

use App\Entity\TermTemplate;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TermTemplate>
 *
 * @method TermTemplate|null find($id, $lockMode = null, $lockVersion = null)
 * @method TermTemplate|null findOneBy(array $criteria, array $orderBy = null)
 * @method TermTemplate[]    findAll()
 * @method TermTemplate[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TermTemplateRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TermTemplate::class);
    }

}
