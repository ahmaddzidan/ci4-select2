<?php
namespace App\Libraries;

/**
 * Library Select2 for Codeigniter 4
 */
class Select2 
{
	/**
	 * [tabel name of select2 source]
	 * @var string
	 */
	protected $_table = '';

	/**
	 * ID for value option on select2, usually primary key of table
	 * <option value="ID"></option>
	 * @var string
	 */
	protected $_id = 'id';

	/**
	 * Text for select2 
	 * <option value="ID">TEXT</option>
	 * @var string
	 */
	protected $_text = 'name';

	/**
	 * Additional data will be attached on response, field of table 
	 * @var array
	 */
	protected $_additional = array();

	/**
	 * total perpage on select2 load
	 * @var integer
	 */
	protected $_perpage = 10;

	/**
	 * $request object from codeigniter
	 * @var [type]
	 */
	protected $request;

	/**
	 * $db object from codeigniter 
	 * @var [type]
	 */
	protected $db;

	/**
	 * $builder object
	 * @var [type]
	 */
	protected $builder;

	function __construct($config = array())
	{
		foreach ($config as $key => $val)
        {
            $this->{'_'.$key} = $val;
        }

        $this->db = \Config\Database::connect();
        $this->builder = $this->db->table($this->_table);

        $this->request = \Config\Services::request();
	}

	/**
	 * @return JSON DATA
	 */
	public function render()
	{
        $search = $this->request->getGet('term');
		$page = $this->request->getGet('page');

        $offset = ($page - 1) * $this->_perpage;

        // filter for showing result
        $this->filter($search);

        if ($offset !== null)
        {
            $this->builder->limit($this->_perpage, $offset);
        }

		$count     = $this->builder->countAllResults(false); 
		$endCount  = $offset + $this->_perpage;
        $morePages = $endCount < $count;

		$select2 = $this->builder->get()->getResult();
        $select2_option = array();

		foreach($select2 as $item)
		{
			$extraData = [];

			if (is_array($this->_additional) && count($this->_additional) > 0) {
				foreach ($this->_additional as $field) {
					$extraData[] = array($field => $item->{$field});
				}
			}

			$select2_option[] = array("id"=> $item->{$this->_id}, "text"=> $item->{$this->_text}, "additional"=> $extraData);
        }

		$response = array(
			"results" => $select2_option,
			"term" => $search,
			"pagination" => array(
			  "more" => $morePages,
			  "count" => $count,
			)
        );

        return $response;
	}

	/**
	 * @param  keyword will be search on select2
	 * @return void
	 */
	public function filter($search)
	{
		if ($this->request->getGet('filter')) {
			$filters = explode('~', $this->request->getGet('filter'));
			$total_filter = count($filters);

			if ($total_filter > 1)
			{
				for ($i=0; $i < $total_filter; $i++) {
					$filter = explode('-', $filters[$i]);
					$this->builder->where(array($filter[0] => $filter[1]));
				}
			}
			else
			{
				$filter = explode('-', $this->request->getGet('filter'));
				$this->builder->where(array($filter[0] => $filter[1]));
			}
		}

		if ($search !== null)
        {
            $this->builder->like($this->_table .'.'. $this->_text, $search);
        }
	}

	public function default($id)
    {
        $item = $this->builder->getWhere([$this->_id => $id]);

        if (!empty($item)) {
            return array($item->{$this->_id} => $item->{$this->_text});
        }
        else
        {
            return array('' => '');
        }
    }

}