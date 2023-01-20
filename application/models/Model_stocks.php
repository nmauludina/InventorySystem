<?php

class Model_stocks extends CI_Model
{

  public function __construct()
  {
    parent::__construct();
  }

  public function getRawStockData($product_id = null, $attribute_value_id = null)
  {
    if ($product_id && $attribute_value_id) {
      $where_clause = array('product_id' => $product_id, 'attribute_value_id' => $attribute_value_id);
      $this->db->select();
      $this->db->from('stocks');
      $this->db->where($where_clause);
      $query = $this->db->get();
      return $query->result_array();
    }
    if ($product_id) {
      $this->db->select();
      $this->db->from('stock');
      $this->db->where('id', $product_id);
      $query = $this->db->get();
      return $query->result_array();
    }
    $this->db->select();
    $this->db->from('stock');
    $query = $this->db->get();
    return $query->result_array();
  }

  public function getStockData()
  {
    $this->db->select('stocks.id, products.name , products.sku , products.price');
    $this->db->select('products.image , products.description, products.brands_id');
    $this->db->select('products.category_id , products.store_id , products.availability , products.attribute_id');
    $this->db->select('attributes.name as attribute_name, attribute_values.value as attribute_value , stocks.qty');

    $this->db->from('stocks');
    $this->db->join('products', 'stocks.product_id=products.id', 'inner');
    $this->db->join('attribute_values', 'stocks.attribute_value_id=attribute_values.id', 'inner');
    $this->db->join('attributes', 'products.attribute_id=attributes.id', 'inner');
    $this->db->order_by('stocks.id', 'asc');
    $query = $this->db->get();
    return $query->result_array();
  }

  public function create($data)
  {
    if ($data) {
      $insert = $this->db->insert('stocks', $data);
      return ($insert == true) ? true : false;
    }
  }
}
