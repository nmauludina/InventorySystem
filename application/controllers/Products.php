<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Products extends Admin_Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->not_logged_in();

        $this->data['page_title'] = 'Products';

        $this->load->model('model_products');
        $this->load->model('model_brands');
        $this->load->model('model_category');
        $this->load->model('model_stores');
        $this->load->model('model_attributes');
        $this->load->model('model_stocks');
    }

    /* 
    * It only redirects to the manage product page
    */
    public function index()
    {
        if (!in_array('viewProduct', $this->permission)) {
            redirect('dashboard', 'refresh');
        }

        $this->render_template('products/index', $this->data);
    }

    public function fetchProductAutocompleteData()
    {
        $returnData = array();

        $conditions['searchTerm'] = $this->input->get('term');
        $conditions['conditions']['availability'] = '1';
        $productData = $this->model_products->getAutocompleteProductData($conditions);

        // Generate array
        if (!empty($productData)) {
            foreach ($productData as $row) {
                $data['id'] = $row['id'];
                $data['value'] = $row['name'];
                array_push($returnData, $data);
            }
        }
        echo json_encode($returnData);
    }

    public function fetchProductDataById($id)
    {
        $data = $this->model_products->getProductData($id);
        echo json_encode($data);
    }

    public function fetchProductAttributeByProductId($id)
    {
        $data = $this->model_stocks->getProductAttributeByProductId($id);
        echo json_encode($data);
    }

    /*
    * It Fetches the products data from the product table 
    * this function is called from the datatable ajax function
    */
    public function fetchProductData()
    {
        $result = array('data' => array());

        $data = $this->model_stocks->getStockData();

        foreach ($data as $key => $value) {

            $store_data = $this->model_stores->getStoresData($value['store_id']);
            // button
            $buttons = '';
            if (in_array('updateProduct', $this->permission)) {
                $buttons .= '<a href="' . base_url('products/update/' . $value['id']) . '" class="btn btn-default"><i class="fa fa-pencil"></i></a>';
            }

            if (in_array('deleteProduct', $this->permission)) {
                $buttons .= ' <button type="button" class="btn btn-default" onclick="removeFunc(' . $value['id'] . ')" data-toggle="modal" data-target="#removeModal"><i class="fa fa-trash"></i></button>';
            }


            $img = '<img src="' . base_url($value['image']) . '" alt="' . $value['name'] . '" class="img-circle" width="50" height="50" />';

            $availability = ($value['availability'] == 1) ? '<span class="label label-success">Active</span>' : '<span class="label label-warning">Inactive</span>';

            $qty_status = '';
            if ($value['qty'] <= 10) {
                $qty_status = '<span class="label label-warning">Low !</span>';
            } else if ($value['qty'] <= 0) {
                $qty_status = '<span class="label label-danger">Out of stock !</span>';
            }


            $result['data'][$key] = array(
                $img,
                $value['sku'],
                $value['name'],
                $value['price'],
                $value['attribute_name'],
                $value['attribute_value'],
                $value['qty'] . ' ' . $qty_status,
                $store_data['name'],
                $availability,
                $buttons
            );
        } // /foreach

        echo json_encode($result);
    }

    /*
    * If the validation is not valid, then it redirects to the create page.
    * If the validation for each input field is valid then it inserts the data into the database 
    * and it stores the operation message into the session flashdata and display on the manage product page
    */
    public function create()
    {
        if (!in_array('createProduct', $this->permission)) {
            redirect('dashboard', 'refresh');
        }

        // get active attribute data
        $attribute_data = $this->model_attributes->getActiveAttributeData();
        $attributes_final_data = array();
        foreach ($attribute_data as $k => $v) {
            array_push($attributes_final_data, $v['name']);
        }

        // set every attribute rule if shown in FE
        foreach ($attributes_final_data as $v) {
            if ($this->input->post('$v')) $this->form_validation->set_rules($v, $v, 'trim|required');
        }

        $this->form_validation->set_rules('name', 'Product name', 'trim|required');
        $this->form_validation->set_rules('sku', 'SKU', 'trim|required');
        $this->form_validation->set_rules('price', 'Price', 'trim|required');
        $this->form_validation->set_rules('qty', 'Qty', 'trim|required');
        $this->form_validation->set_rules('store', 'Store', 'trim|required');
        $this->form_validation->set_rules('availability', 'Availability', 'trim|required');

        if ($this->form_validation->run() == TRUE) {
            // true case
            $upload_image = $this->upload_image();

            $sku = $this->input->post('sku');
            $product = $this->model_products->getProductDataBySku($sku);
            if (empty($product)) { // if product is not in the table create product
                $data_product = array(
                    'name' => $this->input->post('name'),
                    'sku' => $this->input->post('sku'),
                    'price' => $this->input->post('price'),
                    'image' => $upload_image,
                    'description' => $this->input->post('description'),
                    'attribute_id' => $this->input->post('attribute'),
                    'brands_id' => json_encode($this->input->post('brands')),
                    'category_id' => json_encode($this->input->post('category')),
                    'store_id' => $this->input->post('store'),
                    'availability' => $this->input->post('availability'),
                );
                $create_product = $this->model_products->create($data_product);
                $product = $this->model_products->getProductDataBySku($sku);
            } else { // else let create product be 1
                $create_product = 1;
            }

            // if attribute form not empty check stock if there any exist attribute for the product
            if (!empty($product['attribute_id'])) {
                $attribute = $this->model_attributes->getAttributeData($product['attribute_id']);
                $attribute_value_id = $this->input->post($attribute['name']);
                $checkStockExist = $this->model_stocks->getRawStockData($product['id'], $attribute_value_id); // check if data stock exist
            } else {
                // if attribute empty and product not yet registered, add record
                $checkStockExist = $this->model_stocks->getRawStockData($product['id']); // check if data stock exist
            }

            if (empty($checkStockExist)) {
                $data_stock = array(
                    'product_id' => $product['id'],
                    'attribute_value_id' => $attribute_value_id,
                    'qty' => $this->input->post('qty')
                );
                $create_stock_attribute = $this->model_stocks->create($data_stock);
            } else {
                $this->session->set_flashdata('errors', 'Error occurred!! Product with the listed attributes have been registered');
                redirect('products/create', 'refresh');
            }

            if ($create_product == true and $create_stock_attribute == true) {
                $this->session->set_flashdata('success', 'Successfully created');
                redirect('products/', 'refresh');
            } else {
                $this->session->set_flashdata('errors', 'Error occurred!!');
                redirect('products/create', 'refresh');
            }
        } else {
            // false case

            // attributes 
            $attribute_data = $this->model_attributes->getActiveAttributeData();

            $attributes_final_data = array();
            foreach ($attribute_data as $k => $v) {
                $attributes_final_data[$k]['attribute_data'] = $v;

                $value = $this->model_attributes->getAttributeValueData($v['id']);

                $attributes_final_data[$k]['attribute_value'] = $value;
            }

            $this->data['attributes'] = $attributes_final_data;
            $this->data['brands'] = $this->model_brands->getActiveBrands();
            $this->data['category'] = $this->model_category->getActiveCategroy();
            $this->data['stores'] = $this->model_stores->getActiveStore();
            $this->data['products'] = $this->model_products->getProductData();

            $this->render_template('products/create', $this->data);
        }
    }

    /*
    * This function is invoked from another function to upload the image into the assets folder
    * and returns the image path
    */
    public function upload_image()
    {
        // assets/images/product_image
        $config['upload_path'] = 'assets/images/product_image';
        $config['file_name'] =  uniqid();
        $config['allowed_types'] = 'gif|jpg|png';
        $config['max_size'] = '1000';

        // $config['max_width']  = '1024';s
        // $config['max_height']  = '768';

        $this->load->library('upload', $config);
        if (!$this->upload->do_upload('product_image')) {
            $error = $this->upload->display_errors();
            return $error;
        } else {
            $data = array('upload_data' => $this->upload->data());
            $type = explode('.', $_FILES['product_image']['name']);
            $type = $type[count($type) - 1];

            $path = $config['upload_path'] . '/' . $config['file_name'] . '.' . $type;
            return ($data == true) ? $path : false;
        }
    }

    /*
    * If the validation is not valid, then it redirects to the edit product page 
    * If the validation is successfully then it updates the data into the database 
    * and it stores the operation message into the session flashdata and display on the manage product page
    */
    public function update($product_id)
    {
        if (!in_array('updateProduct', $this->permission)) {
            redirect('dashboard', 'refresh');
        }

        if (!$product_id) {
            redirect('dashboard', 'refresh');
        }

        $this->form_validation->set_rules('product_name', 'Product name', 'trim|required');
        $this->form_validation->set_rules('sku', 'SKU', 'trim|required');
        $this->form_validation->set_rules('price', 'Price', 'trim|required');
        $this->form_validation->set_rules('qty', 'Qty', 'trim|required');
        $this->form_validation->set_rules('store', 'Store', 'trim|required');
        $this->form_validation->set_rules('availability', 'Availability', 'trim|required');

        if ($this->form_validation->run() == TRUE) {
            // true case

            $data = array(
                'name' => $this->input->post('product_name'),
                'sku' => $this->input->post('sku'),
                'price' => $this->input->post('price'),
                'qty' => $this->input->post('qty'),
                'description' => $this->input->post('description'),
                'attribute_value_id' => json_encode($this->input->post('attributes_value_id')),
                'brand_id' => json_encode($this->input->post('brands')),
                'category_id' => json_encode($this->input->post('category')),
                'store_id' => $this->input->post('store'),
                'availability' => $this->input->post('availability'),
            );


            if ($_FILES['product_image']['size'] > 0) {
                $upload_image = $this->upload_image();
                $upload_image = array('image' => $upload_image);

                $this->model_products->update($upload_image, $product_id);
            }

            $update = $this->model_products->update($data, $product_id);
            if ($update == true) {
                $this->session->set_flashdata('success', 'Successfully updated');
                redirect('products/', 'refresh');
            } else {
                $this->session->set_flashdata('errors', 'Error occurred!!');
                redirect('products/update/' . $product_id, 'refresh');
            }
        } else {
            // attributes 
            $attribute_data = $this->model_attributes->getActiveAttributeData();

            $attributes_final_data = array();
            foreach ($attribute_data as $k => $v) {
                $attributes_final_data[$k]['attribute_data'] = $v;

                $value = $this->model_attributes->getAttributeValueData($v['id']);

                $attributes_final_data[$k]['attribute_value'] = $value;
            }

            // false case
            $this->data['attributes'] = $attributes_final_data;
            $this->data['brands'] = $this->model_brands->getActiveBrands();
            $this->data['category'] = $this->model_category->getActiveCategroy();
            $this->data['stores'] = $this->model_stores->getActiveStore();

            $product_data = $this->model_products->getProductData($product_id);
            $this->data['product_data'] = $product_data;
            $this->render_template('products/edit', $this->data);
        }
    }

    /*
    * It removes the data from the database
    * and it returns the response into the json format
    */
    public function remove()
    {
        if (!in_array('deleteProduct', $this->permission)) {
            redirect('dashboard', 'refresh');
        }

        $product_id = $this->input->post('product_id');

        $response = array();
        if ($product_id) {
            $delete = $this->model_products->remove($product_id);
            if ($delete == true) {
                $response['success'] = true;
                $response['messages'] = "Successfully removed";
            } else {
                $response['success'] = false;
                $response['messages'] = "Error in the database while removing the product information";
            }
        } else {
            $response['success'] = false;
            $response['messages'] = "Refersh the page again!!";
        }

        echo json_encode($response);
    }
}
