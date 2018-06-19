<?php
/**
 * @version   $Id: Item.php 10887 2013-05-30 06:31:57Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2018 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

class RokSprocket_Item
{
    /**
     * @var
     */
    protected $id;

    /**
     * @var string
     */
    protected $alias;

    /**
     * @var bool
     */
    protected $published;

    /**
     * @var string
     */
    protected $author;

    /**
     * @var string
     */
    protected $date;


    /**
     * @var string
     */
    protected $title;

    /**
     * @var string
     */
    protected $text;

    /**
     * @var string
     */
    protected $provider;

    /**
     * @var RokSprocket_Item_Image
     */
    protected $primaryImage;

    /**
     * @var \RokSprocket_Item_Image[]
     */
    protected $images;

    /**
     * @var RokSprocket_Item_Link
     */
    protected $primaryLink;

    /**
     * @var \RokSprocket_Item_Link[]
     */
    protected $links;

    /**
     * @var RokSprocket_Item_Field[]
     */
    protected $textFields;


    /**
     * @var string[]
     */
    protected $tags;

    /**
     * @var int
     */
    protected $order;

    /**
     * @var int
     */
    protected $commentCount;

    /**
     * @var int
     */
    protected $hits;


    /**
     * @var int
     */
    protected $rating;

    /**
     * @var
     */
    protected $metaKey;
    /**
     * @var
     */
    protected $metaDesc;
    /**
     * @var
     */
    protected $metaData;

    /**
     * @var string
     */
    protected $category;

    /**
     * @var array
     */
    protected $params = array();

    /**
     * @var
     */
    protected $dborder;

    /**
     * @var
     */
    protected $publish_up;

    /**
     * @var
     */
    protected $publish_down;


    /**
     * @param string $alias
     */
    public function setAlias($alias)
    {
        $this->alias = $alias;
    }

    /**
     * @return string
     */
    public function getAlias()
    {
        return $this->alias;
    }

    /**
     * @param string $author
     */
    public function setAuthor($author)
    {
        $this->author = $author;
    }

    /**
     * @return string
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * @param string $date
     */
    public function setDate($date)
    {
        $this->date = $date;
    }

    /**
     * @return string
     */
    public function getDate()
    {
        return $this->date;
    }


    /**
     * @param $fields
     */
    public function setTextFields($fields)
    {
        $this->textFields = $fields;
    }

    /**
     * @return RokSprocket_Item_Field[]
     */
    public function getTextFields()
    {
        return $this->textFields;
    }

    /**
     * @param $identifier
     *
     * @return null|\RokSprocket_Item_Field
     */
    public function getTextField($identifier)
    {
        if (isset($this->textFields[$identifier])) {
            return $this->textFields[$identifier];
        }
        return null;
    }

    /**
     * @param $identifier
     * @param $value
     */
    public function addTextField($identifier, $value)
    {
        $this->textFields[$identifier] = $value;
    }

    /**
     * @param $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param $images
     */
    public function setImages($images)
    {
        $this->images = $images;
    }

    /**
     * @return RokSprocket_Item_Image[]
     */
    public function getImages()
    {
        return $this->images;
    }

    /**
     * @param $identifier
     *
     * @return null|RokSprocket_Item_Image
     */
    public function getImage($identifier)
    {
        if (isset($this->images[$identifier])) {
            return $this->images[$identifier];
        }
        return null;
    }

    /**
     * @param $identifier
     * @param $image
     */
    public function addImage($identifier, RokSprocket_Item_Image $image)
    {
        $this->images[$identifier] = $image;
    }

    /**
     * @param string $introtext
     */
    public function setText($introtext)
    {
        $this->text = $introtext;
    }

    /**
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * @param $links
     */
    public function setLinks($links)
    {
        $this->links = $links;
    }

    /**
     * @return RokSprocket_Item_Link[]
     */
    public function getLinks()
    {
        return $this->links;
    }

    /**
     * @param $identifier
     *
     * @return null|RokSprocket_Item_Link
     */
    public function getLink($identifier)
    {
        if (isset($this->links[$identifier])) {
            return $this->links[$identifier];
        }
        return null;
    }

    /**
     * @param $identifier
     * @param $link
     */
    public function addLink($identifier, RokSprocket_Item_Link $link)
    {
        $this->links[$identifier] = $link;
    }

    /**
     * @param \RokSprocket_Item_Image $primaryImage
     */
    public function setPrimaryImage($primaryImage)
    {
        $this->primaryImage = $primaryImage;
    }

    /**
     * @return \RokSprocket_Item_Image
     */
    public function &getPrimaryImage()
    {
        return $this->primaryImage;
    }

    /**
     * @param \RokSprocket_Item_Link $primaryLink
     */
    public function setPrimaryLink($primaryLink)
    {
        $this->primaryLink = $primaryLink;
    }

    /**
     * @return \RokSprocket_Item_Link
     */
    public function &getPrimaryLink()
    {
        return $this->primaryLink;
    }

    /**
     * @param string $provider
     */
    public function setProvider($provider)
    {
        $this->provider = $provider;
    }

    /**
     * @return string
     */
    public function getProvider()
    {
        return $this->provider;
    }

    /**
     * @param boolean $published
     */
    public function setPublished($published)
    {
        $this->published = $published;
    }

    /**
     * @return boolean
     */
    public function getPublished()
    {
        return $this->published;
    }

    /**
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param int $order
     */
    public function setOrder($order)
    {
        $this->order = $order;
    }

    /**
     * @return int
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @param $tags
     */
    public function setTags($tags)
    {
        $this->tags = $tags;
    }

    /**
     * @return string[]
     */
    public function getTags()
    {
        return $this->tags;
    }

    /**
     * @param $commentCount
     */
    public function setCommentCount($commentCount)
    {
        $this->commentCount = $commentCount;
    }

    /**
     * @return int
     */
    public function getCommentCount()
    {
        return $this->commentCount;
    }

    /**
     * @param int $hits
     */
    public function setHits($hits)
    {
        $this->hits = $hits;
    }

    /**
     * @return int
     */
    public function getHits()
    {
        return $this->hits;
    }

    /**
     * @param int $rating
     */
    public function setRating($rating)
    {
        $this->rating = $rating;
    }

    /**
     * @return int
     */
    public function getRating()
    {
        return $this->rating;
    }

    /**
     * @param  $metaData
     */
    public function setMetaData($metaData)
    {
        $this->metaData = $metaData;
    }

    /**
     * @return
     */
    public function getMetaData()
    {
        return $this->metaData;
    }

    /**
     * @param  $metaDesc
     */
    public function setMetaDesc($metaDesc)
    {
        $this->metaDesc = $metaDesc;
    }

    /**
     * @return
     */
    public function getMetaDesc()
    {
        return $this->metaDesc;
    }

    /**
     * @param  $metaKey
     */
    public function setMetaKey($metaKey)
    {
        $this->metaKey = $metaKey;
    }

    /**
     * @return
     */
    public function getMetaKey()
    {
        return $this->metaKey;
    }

    /**
     * @param $category
     */
    public function setCategory($category)
    {
        $this->category = $category;
    }

    /**
     * @return string
     */
    public function getCategory()
    {
        return $this->category;
    }


    /**
     * @return string
     */
    public function getArticleId()
    {
        return $this->provider . '-' . $this->id;
    }

    /**
     * @param $parameters
     */
    public function setParams($parameters)
    {
        $this->params = $parameters;
    }

    /**
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * @param      $name
     * @param null $default
     *
     * @return null
     */
    public function getParam($name, $default = null)
    {
        if (isset($this->params[$name])) {
            return $this->params[$name];
        } else {
            return $default;
        }
    }

    /**
     * @param $dborder
     */
    public function setDbOrder($dborder)
    {
        $this->dborder = $dborder;
    }

    /**
     * @return mixed
     */
    public function getDbOrder()
    {
        return $this->dborder;
    }

    /**
     * @param $name
     * @param $value
     */
    public function setParam($name, $value)
    {
        $this->params[$name] = $value;
    }

    /**
     * @return mixed
     */
    public function getPublishUp()
    {
        return $this->publish_up;
    }

    /**
     * @param $datetime
     */
    public function setPublishUp($datetime)
    {
        $this->publish_up = $datetime;
    }

    /**
     * @return mixed
     */
    public function getPublishDown()
    {
        return $this->publish_down;
    }

    /**
     * @param $datetime
     */
    public function setPublishDown($datetime)
    {
        $this->publish_down = $datetime;
    }
}
