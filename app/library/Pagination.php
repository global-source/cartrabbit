<?php
namespace CartRabbit\library;

/**
 * Class Pagination
 * @package CartRabbit\library
 */
class Pagination
{

    /**
     * Variable for Assign Pagination Limit
     * @var
     */
    protected $limit;

    /**
     * Variable for Assign Products Per Page
     * @var
     */
    protected $perPage;
    /**
     * @var int
     */
    protected $limit_start = 0;
    /**
     * @var int
     */
    protected $total = 4;

    protected $segments;

    /**
     * Generate Pagination for Products
     */
    public function __construct()
    {
        //
    }

    /**
     * To Set the Limit for display item
     * @param $limit
     */
    public function setLimit($limit)
    {
        $this->limit = $limit;
    }

    /**
     * To Set the Start Value
     * @param $limit_start
     */
    public function setLimitStart($limit_start)
    {
        $this->limit_start = $limit_start;
    }

    /**
     * To Set the Total
     * @param $total
     */
    public function setTotal($total)
    {
        $this->total = $total;
    }

    /**
     * To Set the Limit Count
     * @return int
     */
    public function getLimitCount()
    {
        return $this->limit;
    }

    /**
     *
     */
    public function getFooterLimit()
    {
        //
    }


    public function setSegments($segment)
    {
        $this->segments = $segment;
    }

    public function getSegments()
    {
        return $this->segments;
    }

    /**
     * To Generate Pagination
     * @return string Pagination Snippet
     */
    public function generatePagination($extra = '')
    {
//        $extra = 'page=cartrabbit_order&';
        $server = (isset($_SERVER['HTTPS']) ? 'https://' : 'http://');
        $server_name = $_SERVER['SERVER_NAME'];
        $path = $server . $server_name;
        if (isset($this->segments)) {
            $extra = $this->segments;
        }
        $segment = explode('/', trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/'));
        $segment = implode($segment, '/');
        $pagination = '';
        if (is_array($extra)) {
            $extra = implode('/', $extra);
        }

        // if total number of products is equal to product limit to display,
        // then pagination will not be created.
        if ((int)$this->total != (int)$this->limit) {
            $pagination = '<a href="' . $path . '/' . $segment . '?' . $extra . '&limit=' . $this->limit . '&ppage=0"></a>&nbsp;&nbsp;';
            if ($this->total > $this->limit) {
                $page = ceil($this->total / $this->limit);
                for ($i = 1; $i <= $page; $i++) {
                    $j = $i;
                    $pagination .= '<a href="' . $path . '/' . $segment . '?' . $extra . '&limit=' . $this->limit . '&ppage=' . $i . '">' . $j . '</a>&nbsp;&nbsp;';
                }
            } else {
                $pagination .= '<a href="' . $path . '/' . $segment . '?' . $extra . '&limit=' . $this->limit . '&ppage=0"></a>&nbsp;&nbsp;';
            }
            $pagination .= '<a href="' . $path . '/' . $segment . '?' . $extra . '&limit=' . $this->limit . '&ppage=0"></a>&nbsp;&nbsp;';
        }

        return $pagination;
    }

}