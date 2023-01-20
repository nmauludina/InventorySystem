<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <section class="content-header">
    <h1>
      Manage
      <small>Products</small>
    </h1>
    <ol class="breadcrumb">
      <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
      <li class="active">Products</li>
    </ol>
  </section>

  <!-- Main content -->
  <section class="content">
    <!-- Small boxes (Stat box) -->
    <div class="row">
      <div class="col-md-12 col-xs-12">

        <div id="messages"></div>

        <?php if ($this->session->flashdata('success')) : ?>
          <div class="alert alert-success alert-dismissible" role="alert">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <?php echo $this->session->flashdata('success'); ?>
          </div>
        <?php elseif ($this->session->flashdata('error')) : ?>
          <div class="alert alert-error alert-dismissible" role="alert">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <?php echo $this->session->flashdata('error'); ?>
          </div>
        <?php endif; ?>


        <div class="box">
          <div class="box-header">
            <h3 class="box-title">Add Product</h3>
          </div>
          <!-- /.box-header -->
          <form id="createProductForm" role="form" action="<?php base_url('products/create') ?>" method="post" enctype="multipart/form-data">
            <div class="box-body">

              <?php echo validation_errors(); ?>

              <div class="form-group">

                <label for="product_image">Image</label>
                <div class="kv-avatar">
                  <div class="file-loading">
                    <input id="product_image" class="productForm" name="product_image" type="file">
                  </div>
                </div>
              </div>

              <div class="form-group ui-front">
                <label for="name">Product name</label>
                <input type="text" class="form-control productForm" id="name" name="name" placeholder="Enter product name" />
              </div>

              <div class="form-group">
                <label for="sku">SKU</label>
                <input type="text" class="form-control productForm" id="sku" name="sku" placeholder="Enter sku" autocomplete="off" />
              </div>

              <div class="form-group">
                <label for="price">Price</label>
                <input type="text" class="form-control productForm" id="price" name="price" placeholder="Enter price" autocomplete="off" />
              </div>

              <div class="form-group">
                <label for="description">Description</label>
                <textarea type="text" class="form-control productForm" id="description" name="description" placeholder="Enter description" autocomplete="off"></textarea>
              </div>

              <div class="form-group">
                <label for="attribute">Attribute</label>
                <select class="form-control productForm select_group" id="attribute" name="attribute">
                  <option value=""></option>
                  <?php foreach ($attributes as $k => $v) : ?>
                    <option value="<?php echo $v['attribute_data']['id'] ?>"><?php echo $v['attribute_data']['name'] ?></option>
                  <?php endforeach ?>
                </select>
              </div>

              <div id="attribute-forms"></div>

              <div class="form-group">
                <label for="qty">Qty</label>
                <input type="text" class="form-control productForm" id="qty" name="qty" placeholder="Enter Qty" autocomplete="off" />
              </div>

              <div class="form-group">
                <label for="brands">Brands</label>
                <select class="form-control productForm select_group" id="brands" name="brands[]" multiple="multiple">
                  <?php foreach ($brands as $k => $v) : ?>
                    <option value="<?php echo $v['id'] ?>"><?php echo $v['name'] ?></option>
                  <?php endforeach ?>
                </select>
              </div>

              <div class="form-group">
                <label for="category">Category</label>
                <select class="form-control productForm select_group" id="category" name="category[]" multiple="multiple">
                  <?php foreach ($category as $k => $v) : ?>
                    <option value="<?php echo $v['id'] ?>"><?php echo $v['name'] ?></option>
                  <?php endforeach ?>
                </select>
              </div>

              <div class="form-group">
                <label for="store">Store</label>
                <option value=""></option>
                <select class="form-control productForm select_group" id="store" name="store">
                  <?php foreach ($stores as $k => $v) : ?>
                    <option value="<?php echo $v['id'] ?>"><?php echo $v['name'] ?></option>
                  <?php endforeach ?>
                </select>
              </div>

              <div class="form-group">
                <label for="availability">Availability</label>
                <select class="form-control productForm" id="availability" name="availability">
                  <option value="1">Yes</option>
                  <option value="2">No</option>
                </select>
              </div>

            </div>
            <!-- /.box-body -->

            <div class="box-footer">
              <button type="submit" class="btn btn-primary">Save Changes</button>
              <a href="<?php echo base_url('products/') ?>" class="btn btn-warning">Back</a>
            </div>
          </form>
          <!-- /.box-body -->
        </div>
        <!-- /.box -->
      </div>
      <!-- col-md-12 -->
    </div>
    <!-- /.row -->


  </section>
  <!-- /.content -->
</div>
<!-- /.content-wrapper -->

<style>
  .ui-autocomplete {
    z-index: 9999;
  }
</style>

<script type="text/javascript">
  $(document).ready(function() {
    const base_url = "<?php echo base_url(); ?>";

    $(".select_group").select2();
    $("#description").wysihtml5();

    $("#mainProductNav").addClass('active');
    $("#addProductNav").addClass('active');

    $('#name').autocomplete({
      source: base_url + 'products/fetchProductAutocompleteData',
      select: (e, ui) => {
        let allInputFormId = []
        $('#createProductForm .productForm').each((index, elem) => {
          allInputFormId.push(elem.id)
        })
        allInputFormId.splice(allInputFormId.indexOf('name'), 1) // delete name from array
        allInputFormId.splice(allInputFormId.indexOf('qty'), 1) // delete qty from array

        $.ajax({
          url: base_url + 'products/fetchProductDataById/' + ui.item.id,
          method: "GET",
          async: true,
          dataType: 'json',
          success: function(data) {
            console.log('data', data)
            allInputFormId.forEach(id => {
              if (id === 'description') {
                const wysihtml5Editor = $('#description').data('wysihtml5').editor;
                wysihtml5Editor.setValue(data[id], true)
              }
              if (id === 'brands' || id === 'category' || id === 'store' || id === 'attribute') {
                $(`#${id}`).val(JSON.parse(data[`${id}_id`])).trigger('change')
                console.log(id, data[`${id}_id`])
              }
              $(`#${id}`).val(data[id])
              // $(`#${id}`).prop('disabled', true)
            });
          }
        })
      }
    })

    $("#attribute").on('change', () => {
      const inputAttributeValue = $('#attribute').select2('data')

      if (inputAttributeValue) {
        $('#attribute-forms').empty()
        inputAttributeValue.forEach(({
          id,
          text
        }) => {
          $.ajax({
            url: base_url + 'attributes/fetchAttributeValueDataOnly/' + id,
            method: "GET",
            async: true,
            dataType: 'json',
            success: function(data) {
              let options = '';
              let i = 0
              for (i; i < data.length; i++) {
                options += `<option value="${data[i].id}">${data[i].value}</option>`
              }
              $(`#${text}`).empty()
              $(`#${text}`).append(options)
            }
          })
          $('#attribute-forms').append(`
            <div id="attribute-form-${text}" class="form-group">
              <label for="${text}">${text}</label>
              <select class="form-control productForm" id="${text}" name="${text}">
              </select>
            </div>
          `)
          $(`#${text}`).select2();
        });
      };
    });

    var btnCust = '<button type="button" class="btn btn-secondary" title="Add picture tags" ' +
      'onclick="alert(\'Call your custom code here.\')">' +
      '<i class="glyphicon glyphicon-tag"></i>' +
      '</button>';
    $("#product_image").fileinput({
      overwriteInitial: true,
      maxFileSize: 1500,
      showClose: false,
      showCaption: false,
      browseLabel: '',
      removeLabel: '',
      browseIcon: '<i class="glyphicon glyphicon-folder-open"></i>',
      removeIcon: '<i class="glyphicon glyphicon-remove"></i>',
      removeTitle: 'Cancel or reset changes',
      elErrorContainer: '#kv-avatar-errors-1',
      msgErrorClass: 'alert alert-block alert-danger',
      // defaultPreviewContent: '<img src="/uploads/default_avatar_male.jpg" alt="Your Avatar">',
      layoutTemplates: {
        main2: '{preview} ' + btnCust + ' {remove} {browse}'
      },
      allowedFileExtensions: ["jpg", "png", "gif"]
    });

  });
</script>