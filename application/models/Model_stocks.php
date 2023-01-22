<?php

class Model_stocks extends CI_Model
{

  public function __construct()
  {
    parent::__construct();
  }

  public function getRawStockData($id = null, $product_id = null, $attribute_value_id = null)
  {
    $this->db->select();
    $this->db->from('stocks');
    if ($id) {
      $this->db->where('id', $id);
    }
    if ($product_id) {
      $this->db->where('product_id', $product_id);
    }
    if ($attribute_value_id) {
      $this->db->where('attribute_value_id', $attribute_value_id);
    }
    $query = $this->db->get();
    return $query->result_array();
  }

  public function getStockData($id = null)
  {
    $this->db->select('stocks.id, products.name , products.sku , products.price');
    $this->db->select('products.image , products.description, products.brands_id');
    $this->db->select('products.category_id , products.store_id , products.availability , products.attribute_id');
    $this->db->select('attributes.name as attribute_name, attribute_values.value as attribute_value , stocks.qty');

    $this->db->from('stocks');
    $this->db->join('products', 'stocks.product_id=products.id', 'inner');
    $this->db->join('attribute_values', 'stocks.attribute_value_id=attribute_values.id', 'left');
    $this->db->join('attributes', 'products.attribute_id=attributes.id', 'left');
    $this->db->order_by('stocks.id', 'asc');
    if ($id) {
      $this->db->where('stocks.id', $id);
    }
    $query = $this->db->get();
    return $query->result_array();
  }

  public function getProductStockDataByProductId($id, $attribute_value_id = null)
  {
    $this->db->select('stocks.id, products.id as product_id, products.name , products.sku , products.price');
    $this->db->select('products.image , products.description, products.brands_id');
    $this->db->select('products.category_id , products.store_id , products.availability , products.attribute_id');
    $this->db->select('attributes.name as attribute_name, attribute_values.id as attribute_value_id , attribute_values.value as attribute_value , stocks.qty');

    $this->db->from('stocks');
    $this->db->join('products', 'stocks.product_id=products.id', 'inner');
    $this->db->join('attribute_values', 'stocks.attribute_value_id=attribute_values.id', 'inner');
    $this->db->join('attributes', 'products.attribute_id=attributes.id', 'inner');

    $this->db->order_by('stocks.id', 'asc');
    $this->db->where('products.id', $id);
    if ($attribute_value_id) {
      $this->db->where('attribute_values.id', $attribute_value_id);
    }
    $query = $this->db->get();
    return $query->result_array();
  }

  public function getProductAttributeByProductId($product_id = null)
  {
    $this->db->select('products.id, products.name, products.sku, products.attribute_id');
    $this->db->select('attributes.name as attribute_name, stocks.attribute_value_id, attribute_values.value as attribute_value');

    $this->db->from('stocks');
    $this->db->join('products', 'stocks.product_id=products.id', 'inner');
    $this->db->join('attribute_values', 'stocks.attribute_value_id=attribute_values.id', 'inner');
    $this->db->join('attributes', 'products.attribute_id=attributes.id', 'inner');
    $this->db->order_by('products.id', 'asc');
    if (isset($product_id)) {
      $this->db->where('products.id', $product_id);
    }
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

  public function update($data, $id)
  {
    if ($data && $id) {
      $this->db->where('id', $id);
      $update = $this->db->update('stocks', $data);
      return ($update == true) ? true : false;
    }
  }
}
