<?php
namespace Asanusi007\Select2;


/**
 * Name:    Select2
 *
 * Created:  22.01.2023
 *
 * Description:  Select2 Adapter for CI 4.x.
 *
 * Requirements: PHP 7.2 or above
 *
 * @package    CodeIgniter-Select2-Adapter
 * @author     Ahmad Sanusi <asanusi007@gmail.com>
 * @license    https://opensource.org/licenses/MIT	MIT License
 * @copyright 2023 Ahmad Sanusi and Github contributors
 * @link       https://github.com/ahmaddzidan/
 * @link       https://ahmadsanusi.com/
 * @filesource
 */

/**
 * This class is the Select2 library.
 */
class Select2
{

    /**
     * Table
     *
     * @var string table name
     */
    protected $table;

    /**
     * Primary Key
     *
     * Value for select option
     * <option value="{Value}">{TEXT}</option>
     *
     * @var int|string primary key
     */
    protected $primaryKey = 'id';

    /**
     * Text Property
     *
     * Text for select option
     * <option value="{ID}">{TEXT}</option>
     *
     * @var string Text Property
     */
    protected $textProperty;

    /**
     * Page Limit
     *
     * @var int
     */
    protected $pageLimit = 10;

    /**
     * Additional data will be attached on response, field of table
     *
     * @var array
     */
    protected $additional = [];

    /**
     *
     * Searchable Fields
     *
     * @var array
     */
    protected $searchableFields = [];

    /**
     *
     * Wheres Fields
     *
     * @var array
     */
    protected $wheres = [];

    /**
     * Database object
     *
     * @var \CodeIgniter\Database\BaseConnection $db
     */
    protected $db;

    /**
     * Bulder object
     *
     * @var \CodeIgniter\Database\BaseBuilder $builder
     */
    protected $builder;

    /**
     * Constructor
     *
     * @author Ahmad Sanusi
     *
     * @param array $config
     *
     * @return void
     */
    public function __construct($config = [])
    {
        $this->db = db_connect();

        $this->init($config);
    }

    /**
     * Initialize library
     *
     * @author Ahmad Sanusi
     *
     * @param array $config
     *
     * @return void
     */
    public function init($config)
    {
        foreach ($config as $key => $val) {
            $this->{$key} = $val;
        }

        $this->builder = $this->db->table($this->table);
    }

    /**
     * Render
     *
     * Render the request
     *
     * @return array
     */
    public function render()
    {
        $term = request()->getGet('term');
        $page = request()->getGet('page') ?? 1;
        $offset = ($page - 1) * $this->pageLimit;

        if (request()->getGet('filter')) {
            $filters = json_decode(request()->getGet('filter'), true);

            foreach ($filters as $field => $value) {
                $this->builder->where($field, $value);
            }

        }

        if (count($this->wheres) > 0) {
            foreach ($this->wheres as $field => $val) {
                $this->builder->where($field, $val);
            }
        }

        if ($term !== null) {
            if (is_array($this->searchableFields) && count($this->searchableFields) > 1) {
                $this->builder->groupStart();

                $this->builder->like($this->table . '.' . $this->textProperty, $term, "both", null, true);

                foreach ($this->searchableFields as $field) {
                    $this->builder->orLike($this->table . '.' . $field, $term, "both", null, true);
                }

                $this->builder->groupEnd();
            } else {
                $this->builder->like($this->table . '.' . $this->textProperty, $term, "both", null, true);
            }
        }

        if ($offset >= 0) {
            $this->builder->limit($this->pageLimit, $offset);
        }


        $count = $this->builder->countAllResults(false);
        $endCount = $offset + $this->pageLimit;
        $morePages = $endCount < $count;

        $results = [];
        $optionList = $this->builder->get()->getResult('array');

        foreach ($optionList as $item) {
            if (is_array($this->searchableFields)) {
                $temp = [];

                foreach ($this->searchableFields as $field) {
                    $temp[] = $item[$field];
                }

                $text = implode(" - ", $temp);

            } else {
                $text = $item[$this->textProperty];
            }

            $results[] = array(
                "id" => $item[$this->primaryKey],
                "text" => $text,
                "row" => $item
            );
        }

        $response = array(
            "results" => $results,
            "term" => $term,
            "rows" => $count,
            "endCount" => $endCount,
            "pagination" => array(
                "more" => $morePages
            )
        );

        return $response;
    }

    /**
     * Default
     *
     * Default selected option
     *
     * @param int|string $id
     *
     * @return array $response
     */

    public function default($id)
    {
        $item = $this->builder->where([$this->primaryKey => $id])->get()->getRowArray();

        if (is_array($this->searchableFields)) {
            $temp = [];

            foreach ($this->searchableFields as $field) {
                $temp[] = $item[$field];
            }

            $text = implode(" - ", $temp);

        } else {
            $text = $item[$this->textProperty];
        }

        if (!empty($item)) {
            $response = array($item[$this->primaryKey] => $text);
        } else {
            $response = array('' => '');
        }

        return $response;
    }
}