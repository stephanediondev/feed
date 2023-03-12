<?php

namespace App\Repository;

use App\Repository\AbstractRepository;
use App\Entity\Category;
use App\Entity\ActionCategory;
use App\Entity\FeedCategory;

class CategoryRepository extends AbstractRepository
{
    public function getEntityClass(): string
    {
        return Category::class;
    }

    public function getOne($parameters = [])
    {
        $em = $this->getEntityManager();

        $query = $em->createQueryBuilder();
        $query->addSelect('cat');
        $query->from(Category::class, 'cat');

        if (isset($parameters['id']) == 1) {
            $query->andWhere('cat.id = :id');
            $query->setParameter(':id', $parameters['id']);
        }

        if (isset($parameters['title']) == 1) {
            $query->andWhere('cat.title = :title');
            $query->setParameter(':title', $parameters['title']);
        }

        $getQuery = $query->getQuery();
        $getQuery->setMaxResults(1);

        if ($cacheDriver = $this->cacheDriver()) {
            $cacheDriver->setNamespace('readerself.category.');
            $getQuery->setResultCacheDriver($cacheDriver);
            $getQuery->setResultCacheLifetime(86400);
        }

        return $getQuery->getOneOrNullResult();
    }

    public function getList($parameters = [])
    {
        $em = $this->getEntityManager();

        if (isset($parameters['trendy']) == 1 && $parameters['trendy']) {
            if (isset($parameters['member'])) {
                $exclude = 'AND cat.id NOT IN( SELECT category_id FROM action_category WHERE member_id = :member_id AND action_id = :action_id )';
            } else {
                $exclude = '';
            }

            $sql = <<<SQL
SELECT LOWER(cat.title) AS ref, cat.id AS id, COUNT(DISTINCT(cat_itm.item_id)) AS count
FROM item AS itm
LEFT JOIN item_category AS cat_itm ON cat_itm.item_id = itm.id
LEFT JOIN category AS cat ON cat.id = cat_itm.category_id
WHERE itm.date >= :date_ref AND cat.title != '' $exclude
GROUP BY ref
ORDER BY count DESC
LIMIT 0,100
SQL;

            $date_ref = date('Y-m-d H:i:s', time() - 3600 * 24 * 30);

            $stmt = $em->getConnection()->prepare($sql);
            $stmt->bindValue('date_ref', $date_ref);
            if (isset($parameters['member'])) {
                $stmt->bindValue('member_id', $parameters['member']->getId());
                $stmt->bindValue('action_id', 5);
            }
            $resultSet = $stmt->executeQuery();
            $results = $resultSet->fetchAllAssociative();

            return $results;
        }

        $query = $em->createQueryBuilder();
        $query->addSelect('cat.id');
        $query->from(Category::class, 'cat');

        if (isset($parameters['excluded']) == 1 && $parameters['excluded']) {
            $query->andWhere('cat.id IN (SELECT IDENTITY(excluded.category) FROM '.ActionCategory::class.' AS excluded WHERE excluded.member = :member AND excluded.action = 5)');
            $query->setParameter(':member', $parameters['member']);
        }

        if (isset($parameters['usedbyfeeds']) == 1 && $parameters['usedbyfeeds']) {
            $query->andWhere('cat.id IN (SELECT IDENTITY(feed.category) FROM '.FeedCategory::class.' AS feed)');
        }

        if (isset($parameters['days']) == 1) {
            $query->andWhere('cat.dateCreated > :date');
            $query->setParameter(':date', new \DateTime('-'.$parameters['days'].' days'));
        }

        $query->addOrderBy($parameters['sortField'], $parameters['sortDirection']);
        $query->groupBy('cat.id');

        if (true === isset($parameters['returnQueryBuilder']) && true === $parameters['returnQueryBuilder']) {
            return $query;
        }

        $getQuery = $query->getQuery();
        return $getQuery;
    }
}
