<?php
/*-------------------------------------------------------
*
*   LiveStreet Engine Social Networking
*   Copyright © 2008 Mzhelskiy Maxim
*
*--------------------------------------------------------
*
*   Official site: www.livestreet.ru
*   Contact e-mail: rus.engine@gmail.com
*
*   GNU General Public License, version 2:
*   http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
*
---------------------------------------------------------
*/

namespace App\Mappers;

use App\Entities\EntityBlog;
use App\Entities\EntityBlogUser;
use App\Modules\ModuleBlog;
use Engine\Config;
use Engine\Mapper;

/**
 * Маппер для работы с БД по части блогов
 *
 * @package modules.blog
 * @since   1.0
 */
class MapperBlog extends Mapper
{
    /**
     * Добавляет блог в БД
     *
     * @param EntityBlog $oBlog Объект блога
     *
     * @return int|bool
     */
    public function AddBlog(EntityBlog $oBlog)
    {
        $sql = "INSERT INTO ".Config::Get('db.table.blog')." 
			(user_owner_id,
			blog_title,
			blog_description,
			blog_type,			
			blog_date_add,
			blog_limit_rating_topic,
			blog_url,
			blog_avatar
			)
			VALUES(?d,  ?,	?,	?,	?,	?, ?, ?)
		";
        if ($iId = $this->oDb->query(
            $sql,
            $oBlog->getOwnerId(),
            $oBlog->getTitle(),
            $oBlog->getDescription(),
            $oBlog->getType(),
            $oBlog->getDateAdd(),
            $oBlog->getLimitRatingTopic(),
            $oBlog->getUrl(),
            $oBlog->getAvatar()
        )
        ) {
            return $iId;
        }

        return false;
    }

    /**
     * Обновляет блог в БД
     *
     * @param EntityBlog $oBlog Объект блога
     *
     * @return bool
     */
    public function UpdateBlog(EntityBlog $oBlog)
    {
        $sql = "UPDATE ".Config::Get('db.table.blog')." 
			SET 
				blog_title= ?,
				blog_description= ?,
				blog_type= ?,
				blog_date_edit= ?,
				blog_rating= ?f,
				blog_count_vote = ?d,
				blog_count_user= ?d,
				blog_count_topic= ?d,
				blog_limit_rating_topic= ?f ,
				blog_url= ?,
				blog_avatar= ?,
				blog_deleted= ?
			WHERE
				blog_id = ?d
		";
        if ($this->oDb->query(
            $sql,
            $oBlog->getTitle(),
            $oBlog->getDescription(),
            $oBlog->getType(),
            $oBlog->getDateEdit(),
            $oBlog->getRating(),
            $oBlog->getCountVote(),
            $oBlog->getCountUser(),
            $oBlog->getCountTopic(),
            $oBlog->getLimitRatingTopic(),
            $oBlog->getUrl(),
            $oBlog->getAvatar(),
            $oBlog->getDeleted() ? 1 : 0,
            $oBlog->getId()
        )
        ) {
            return true;
        }

        return false;
    }

    /**
     * Получает список блогов по ID
     *
     * @param array      $aArrayId Список ID блогов
     * @param array|null $aOrder   Сортировка блогов
     *
     * @return array
     */
    public function GetBlogsByArrayId($aArrayId, $aOrder = null)
    {
        if (!is_array($aArrayId) or count($aArrayId) == 0) {
            return [];
        }

        if (!is_array($aOrder)) {
            $aOrder = [$aOrder];
        }
        $sOrder = '';
        foreach ($aOrder as $key => $value) {
            $value = (string)$value;
            if (!in_array(
                $key,
                ['blog_id', 'blog_title', 'blog_type', 'blog_rating', 'blog_count_user', 'blog_date_add']
            )
            ) {
                unset($aOrder[$key]);
            } elseif (in_array($value, ['asc', 'desc'])) {
                $sOrder .= " {$key} {$value},";
            }
        }
        $sOrder = trim($sOrder, ',');

        $sql = "SELECT 
					*							 
				FROM 
					".Config::Get('db.table.blog')."
				WHERE 
					blog_id IN(?a)
				ORDER BY 						
					{ FIELD(blog_id,?a) } ";
        if ($sOrder != '') {
            $sql .= $sOrder;
        }

        $aBlogs = [];
        if ($aRows = $this->oDb->select($sql, $aArrayId, $sOrder == '' ? $aArrayId : DBSIMPLE_SKIP)) {
            foreach ($aRows as $aBlog) {
                $aBlogs[] = new EntityBlog($aBlog);
            }
        }

        return $aBlogs;
    }

    /**
     * Добавляет свзяь пользователя с блогом в БД
     *
     * @param \App\Entities\EntityBlogUser $oBlogUser Объект отношения пользователя с блогом
     *
     * @return bool
     */
    public function AddRelationBlogUser(EntityBlogUser $oBlogUser)
    {
        $sql = "INSERT INTO ".Config::Get('db.table.blog_user')." 
			(blog_id,
			user_id,
			user_role
			)
			VALUES(?d,  ?d, ?d)
		";
        if ($this->oDb->query($sql, $oBlogUser->getBlogId(), $oBlogUser->getUserId(), $oBlogUser->getUserRole())
            === 0
        ) {
            return true;
        }

        return false;
    }

    /**
     * Удаляет отношение пользователя с блогом
     *
     * @param \App\Entities\EntityBlogUser $oBlogUser Объект отношения пользователя с блогом
     *
     * @return bool
     */
    public function DeleteRelationBlogUser(EntityBlogUser $oBlogUser)
    {
        $sql = "DELETE FROM ".Config::Get('db.table.blog_user')." 
			WHERE
				blog_id = ?d
				AND
				user_id = ?d
		";
        if ($this->oDb->query($sql, $oBlogUser->getBlogId(), $oBlogUser->getUserId())) {
            return true;
        }

        return false;
    }

    /**
     * Обновляет отношение пользователя с блогом
     *
     * @param \App\Entities\EntityBlogUser $oBlogUser Объект отношения пользователя с блогом
     *
     * @return bool
     */
    public function UpdateRelationBlogUser(EntityBlogUser $oBlogUser)
    {
        $sql = "UPDATE ".Config::Get('db.table.blog_user')." 
			SET 
				user_role = ?d			
			WHERE
				blog_id = ?d 
				AND
				user_id = ?d
		";
        if ($this->oDb->query($sql, $oBlogUser->getUserRole(), $oBlogUser->getBlogId(), $oBlogUser->getUserId())) {
            return true;
        }

        return false;
    }

    /**
     * Получает список отношений пользователей с блогами
     *
     * @param array $aFilter   Фильтр поиска отношений
     * @param int   $iCount    Возвращает общее количество элементов
     * @param int   $iCurrPage Номер текущейс страницы
     * @param int   $iPerPage  Количество элементов на одну страницу
     *
     * @return array
     */
    public function GetBlogUsers($aFilter, &$iCount = null, $iCurrPage = null, $iPerPage = null)
    {
        $sWhere = ' 1=1 ';
        if (isset($aFilter['blog_id'])) {
            $sWhere .= " AND bu.blog_id =  ".(int)$aFilter['blog_id'];
        }
        if (isset($aFilter['user_id'])) {
            $sWhere .= " AND bu.user_id =  ".(int)$aFilter['user_id'];
        }
        if (isset($aFilter['user_role'])) {
            if (!is_array($aFilter['user_role'])) {
                $aFilter['user_role'] = [$aFilter['user_role']];
            }
            $sWhere .= " AND bu.user_role IN ('".join("', '", $aFilter['user_role'])."')";
        } else {
            $sWhere .= " AND bu.user_role>".ModuleBlog::BLOG_USER_ROLE_GUEST;
        }
        $sWhere .= " AND b.blog_deleted = 0";
        $sWhere .= " AND bu.blog_id = b.blog_id";

        $sql = "SELECT
					bu.*				
				FROM 
					".Config::Get('db.table.blog_user')." as bu,
					".Config::Get('db.table.blog')." as b	
				WHERE 
					".$sWhere." ";

        if (is_null($iCurrPage)) {
            $aRows = $this->oDb->select($sql);
        } else {
            $sql .= " LIMIT ?d, ?d ";
            $aRows = $this->oDb->selectPage($iCount, $sql, ($iCurrPage - 1) * $iPerPage, $iPerPage);
        }

        $aBlogUsers = [];
        if ($aRows) {
            foreach ($aRows as $aUser) {
                $aBlogUsers[] = new EntityBlogUser($aUser);
            }
        }

        return $aBlogUsers;
    }

    public function GetBlogUsersLike($aFilter, &$iCount = null, $iCurrPage = null, $iPerPage = null)
    {
        $sWhere = ' 1=1 ';
        if (isset($aFilter['blog_id'])) {
            $sWhere .= " AND bu.blog_id =  ".(int)$aFilter['blog_id'];
        }
        if (isset($aFilter['user_id'])) {
            $sWhere .= " AND bu.user_id =  ".(int)$aFilter['user_id'];
        }
        if (isset($aFilter['user_login'])) {
            $sWhere .= " AND bu.user_id IN (SELECT user_id FROM ".Config::Get('db.table.user')
                ." WHERE user_login LIKE \"".$aFilter['user_login']."\")  ";
        }
        if (isset($aFilter['user_role'])) {
            if (!is_array($aFilter['user_role'])) {
                $aFilter['user_role'] = [$aFilter['user_role']];
            }
            $sWhere .= " AND bu.user_role IN ('".join("', '", $aFilter['user_role'])."')";
        } else {
            $sWhere .= " AND bu.user_role>".ModuleBlog::BLOG_USER_ROLE_GUEST;
        }
        $sWhere .= " AND b.blog_deleted = 0";
        $sWhere .= " AND bu.blog_id = b.blog_id";

        $sql = "SELECT
					bu.*				
				FROM 
					".Config::Get('db.table.blog_user')." as bu,
					".Config::Get('db.table.blog')." as b	
				WHERE 
					".$sWhere." ";

        if (is_null($iCurrPage)) {
            $aRows = $this->oDb->select($sql);
        } else {
            $sql .= " LIMIT ?d, ?d ";
            $aRows = $this->oDb->selectPage($iCount, $sql, ($iCurrPage - 1) * $iPerPage, $iPerPage);
        }

        $aBlogUsers = [];
        if ($aRows) {
            foreach ($aRows as $aUser) {
                $aBlogUsers[] = new EntityBlogUser($aUser);
            }
        }

        return $aBlogUsers;
    }

    /**
     * Получает список отношений пользователя к блогам
     *
     * @param array $aArrayId Список ID блогов
     * @param int   $sUserId  ID блогов
     *
     * @return array
     */
    public function GetBlogUsersByArrayBlog($aArrayId, $sUserId)
    {
        if (!is_array($aArrayId) or count($aArrayId) == 0) {
            return [];
        }

        $sql = "SELECT 
					bu.*				
				FROM 
					".Config::Get('db.table.blog_user')." as bu,
					".Config::Get('db.table.blog')." as b	
				WHERE 
					bu.blog_id IN(?a) 
					AND
					bu.blog_id = b.blog_id
					AND
					bu.user_id = ?d";
        $aBlogUsers = [];
        if ($aRows = $this->oDb->select($sql, $aArrayId, $sUserId)) {
            foreach ($aRows as $aUser) {
                $aBlogUsers[] = new EntityBlogUser($aUser);
            }
        }

        return $aBlogUsers;
    }

    /**
     * Получает ID персонального блога пользователя
     *
     * @param int $sUserId ID пользователя
     *
     * @return int|null
     */
    public function GetPersonalBlogByUserId($sUserId)
    {
        $sql = "SELECT blog_id FROM ".Config::Get('db.table.blog')." WHERE user_owner_id = ?d and blog_type='personal'";
        if ($aRow = $this->oDb->selectRow($sql, $sUserId)) {
            return $aRow['blog_id'];
        }

        return null;
    }

    /**
     * Получает блог по названию
     *
     * @param string $sTitle Нащвание блога
     *
     * @return EntityBlog|null
     */
    public function GetBlogByTitle($sTitle)
    {
        $sql = "SELECT blog_id FROM ".Config::Get('db.table.blog')." WHERE blog_title = ? AND blog_deleted = 0";
        if ($aRow = $this->oDb->selectRow($sql, $sTitle)) {
            return $aRow['blog_id'];
        }

        return null;
    }

    /**
     * Получает блог по URL
     *
     * @param string $sUrl URL блога
     *
     * @return EntityBlog|null
     */
    public function GetBlogByUrl($sUrl)
    {
        $sql = "SELECT 
				b.blog_id 
			FROM 
				".Config::Get('db.table.blog')." as b
			WHERE 
				b.blog_url = ?
			AND 
			  	b.blog_deleted = 0
				";
        if ($aRow = $this->oDb->selectRow($sql, $sUrl)) {
            return $aRow['blog_id'];
        }

        return null;
    }

    /**
     * Получить список блогов по хозяину
     *
     * @param int $sUserId ID пользователя
     *
     * @return array
     */
    public function GetBlogsByOwnerId($sUserId)
    {
        $sql = "SELECT 
			b.blog_id			 
			FROM 
				".Config::Get('db.table.blog')." as b				
			WHERE 
				b.user_owner_id = ?
				AND b.blog_deleted = 0
				";
        $aBlogs = [];
        if ($aRows = $this->oDb->select($sql, $sUserId)) {
            foreach ($aRows as $aBlog) {
                $aBlogs[] = $aBlog['blog_id'];
            }
        }

        return $aBlogs;
    }

    /**
     * Возвращает список всех не персональных блогов
     *
     * @return array
     */
    public function GetBlogs()
    {
        $sql = "SELECT 
			b.blog_id			 
			FROM 
				".Config::Get('db.table.blog')." as b				
			WHERE b.blog_deleted = 0";
        $aBlogs = [];
        if ($aRows = $this->oDb->select($sql)) {
            foreach ($aRows as $aBlog) {
                $aBlogs[] = $aBlog['blog_id'];
            }
        }

        return $aBlogs;
    }

    /**
     * Возвращает список не персональных блогов с сортировкой по рейтингу
     *
     * @param int $iCount    Возвращает общее количество элементов
     * @param int $iCurrPage Номер текущей страницы
     * @param int $iPerPage  Количество элементов на одну страницу
     *
     * @return array
     */
    public function GetBlogsRating(&$iCount, $iCurrPage, $iPerPage)
    {
        $sql = "SELECT 
					b.blog_id													
				FROM 
					".Config::Get('db.table.blog')." as b
				WHERE b.blog_deleted = 0
				ORDER by b.blog_rating desc
				LIMIT ?d, ?d 	";
        $aReturn = [];
        if ($aRows = $this->oDb->selectPage($iCount, $sql, ($iCurrPage - 1) * $iPerPage, $iPerPage)) {
            foreach ($aRows as $aRow) {
                $aReturn[] = $aRow['blog_id'];
            }
        }

        return $aReturn;
    }

    /**
     * Получает список блогов в которых состоит пользователь
     *
     * @param int $sUserId ID пользователя
     * @param int $iLimit  Ограничение на выборку элементов
     *
     * @return array
     */
    public function GetBlogsRatingJoin($sUserId, $iLimit)
    {
        $sql = "SELECT 
					b.*													
				FROM 
					".Config::Get('db.table.blog_user')." as bu,
					".Config::Get('db.table.blog')." as b	
				WHERE 	
					bu.user_id = ?d
					AND
					bu.blog_id = b.blog_id
					AND 
					b.blog_deleted = 0
				ORDER by b.blog_rating desc
				LIMIT 0, ?d 
				;	
					";
        $aReturn = [];
        if ($aRows = $this->oDb->select($sql, $sUserId, $iLimit)) {
            foreach ($aRows as $aRow) {
                $aReturn[] = new EntityBlog($aRow);
            }
        }

        return $aReturn;
    }

    /**
     * Получает список блогов, которые создал пользователь
     *
     * @param int $sUserId ID пользователя
     * @param int $iLimit  Ограничение на выборку элементов
     *
     * @return array
     */
    public function GetBlogsRatingSelf($sUserId, $iLimit)
    {
        $sql = "SELECT 
					b.*													
				FROM 					
					".Config::Get('db.table.blog')." as b	
				WHERE 						
					b.user_owner_id = ?d
					AND 
					blog_deleted = 0
				ORDER by b.blog_rating desc
				LIMIT 0, ?d 
			;";
        $aReturn = [];
        if ($aRows = $this->oDb->select($sql, $sUserId, $iLimit)) {
            foreach ($aRows as $aRow) {
                $aReturn[] = new EntityBlog($aRow);
            }
        }

        return $aReturn;
    }

    /**
     * Возвращает полный список закрытых блогов
     *
     * @return array
     */
    public function GetCloseBlogs()
    {
        $sql = "SELECT b.blog_id										
				FROM ".Config::Get('db.table.blog')." as b					
				WHERE b.blog_type='close'
				AND blog_deleted = 0
			;";
        $aReturn = [];
        if ($aRows = $this->oDb->select($sql)) {
            foreach ($aRows as $aRow) {
                $aReturn[] = $aRow['blog_id'];
            }
        }

        return $aReturn;
    }

    /**
     * Возвращает полный список полузакрытых блогов
     *
     * @return array
     */

    public function GetHalfcloseBlogs()
    {
        $sql = "SELECT b.blog_id
                                FROM ".Config::Get('db.table.blog')." as b
                                WHERE b.blog_type='invite'
                                AND blog_deleted = 0
                        ;";
        $aReturn = [];
        if ($aRows = $this->oDb->select($sql)) {
            foreach ($aRows as $aRow) {
                $aReturn[] = $aRow['blog_id'];
            }
        }

        return $aReturn;
    }

    /**
     * Удаление блога из базы данных
     *
     * @param  int $iBlogId ID блога
     *
     * @return bool
     */
    public function DeleteBlog($iBlogId)
    {
        $sql = "
			DELETE FROM ".Config::Get('db.table.blog')." 
			WHERE blog_id = ?d				
		";
        if ($this->oDb->query($sql, $iBlogId)) {
            return true;
        }

        return false;
    }

    /**
     * Удалить пользователей блога по идентификатору блога
     *
     * @param  int $iBlogId ID блога
     *
     * @return bool
     */
    public function DeleteBlogUsersByBlogId($iBlogId)
    {
        $sql = "
			DELETE FROM ".Config::Get('db.table.blog_user')." 
			WHERE blog_id = ?d
		";
        if ($this->oDb->query($sql, $iBlogId)) {
            return true;
        }

        return false;
    }

    /**
     * Пересчитывает число топиков в блогах
     *
     * @param int|null $iBlogId ID блога
     *
     * @return bool
     */
    public function RecalculateCountTopic($iBlogId = null)
    {
        $sql = "
                UPDATE ".Config::Get('db.table.blog')." b
                SET b.blog_count_topic = (
                    SELECT count(*)
                    FROM ".Config::Get('db.table.topic')." t
                    WHERE
                        t.blog_id = b.blog_id
                    AND
                        t.topic_publish = 1
                    AND
                    	t.topic_deleted = 0
                )
                WHERE 1=1
                	{ and b.blog_id = ?d }
            ";
        if ($this->oDb->query($sql, is_null($iBlogId) ? DBSIMPLE_SKIP : $iBlogId)) {
            return true;
        }

        return false;
    }

    /**
     * Получает список блогов по фильтру
     *
     * @param array $aFilter   Фильтр выборки
     * @param array $aOrder    Сортировка
     * @param int   $iCount    Возвращает общее количество элментов
     * @param int   $iCurrPage Номер текущей страницы
     * @param int|0 $bIsDeleted    Удаленные или активные
     *
     * @return array
     */
    public function GetBlogsByFilter($aFilter, $aOrder, &$iCount, $iCurrPage, $iPerPage)
    {
        $aOrderAllow = ['blog_id', 'blog_title', 'blog_rating', 'blog_count_user', 'blog_count_topic'];
        $sOrder = '';
        foreach ($aOrder as $key => $value) {
            if (!in_array($key, $aOrderAllow)) {
                unset($aOrder[$key]);
            } elseif (in_array($value, ['asc', 'desc'])) {
                $sOrder .= " {$key} {$value},";
            }
        }
        $sOrder = trim($sOrder, ',');
        if ($sOrder == '') {
            $sOrder = ' blog_id desc ';
        }

        if (isset($aFilter['exclude_type']) and !is_array($aFilter['exclude_type'])) {
            $aFilter['exclude_type'] = [$aFilter['exclude_type']];
        }
        if (isset($aFilter['type']) and !is_array($aFilter['type'])) {
            $aFilter['type'] = [$aFilter['type']];
        }

        $sql = "SELECT
					blog_id
				FROM
					".Config::Get('db.table.blog')."
				WHERE
					1 = 1
					{ AND blog_id = ?d }
					{ AND user_owner_id = ?d }
					{ AND blog_type IN (?a) }
					{ AND blog_type not IN (?a) }
					{ AND blog_url = ? }
					{ AND blog_title LIKE ? }
					{ AND blog_deleted = ? }
				ORDER by {$sOrder}
				LIMIT ?d, ?d ;
					";
        $aResult = [];
        if ($aRows = $this->oDb->selectPage(
            $iCount,
            $sql,
            isset($aFilter['id']) ? $aFilter['id'] : DBSIMPLE_SKIP,
            isset($aFilter['user_owner_id']) ? $aFilter['user_owner_id'] : DBSIMPLE_SKIP,
            (isset($aFilter['type']) and count($aFilter['type'])) ? $aFilter['type'] : DBSIMPLE_SKIP,
            (isset($aFilter['exclude_type']) and count($aFilter['exclude_type'])) ? $aFilter['exclude_type']
                : DBSIMPLE_SKIP,
            isset($aFilter['url']) ? $aFilter['url'] : DBSIMPLE_SKIP,
            isset($aFilter['title']) ? $aFilter['title'] : DBSIMPLE_SKIP,
            isset($aFilter['deleted']) ? $aFilter['deleted'] : 0,
            ($iCurrPage - 1) * $iPerPage,
            $iPerPage
        )
        ) {
            foreach ($aRows as $aRow) {
                $aResult[] = $aRow['blog_id'];
            }
        }

        return $aResult;
    }
}
