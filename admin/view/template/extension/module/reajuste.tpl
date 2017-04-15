<?php echo $header; ?><?php echo $column_left; ?>
<div id="content">
  <div class="page-header">
    <div class="container-fluid">
      <div class="pull-right">
        <button type="submit" form="form-reajuste" data-toggle="tooltip" title="Aplicar alterações" class="btn btn-primary"><i class="fa fa-save"></i></button>
        <a href="<?php echo $cancel; ?>" data-toggle="tooltip" title="<?php echo $button_cancel; ?>" class="btn btn-default"><i class="fa fa-reply"></i></a></div>
      <h1><?php echo $heading_title; ?></h1>
      <ul class="breadcrumb">
        <?php foreach ($breadcrumbs as $breadcrumb) { ?>
        <li><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a></li>
        <?php } ?>
      </ul>
    </div>
  </div>
  <div class="container-fluid">
    <?php if ($error_warning) { ?>
    <div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> <?php echo $error_warning; ?>
      <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>
    <?php } ?>
    <?php if ($success) { ?>
    <div class="alert alert-success"><i class="fa fa-check-circle"></i> <?php echo $success; ?>
      <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>
    <?php } ?>
    <div class="panel panel-default">
      <div class="panel-heading">
        <h3 class="panel-title"><i class="fa fa-pencil"></i> Reajustar preços</h3>
      </div>
      <div class="panel-body">
        <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form-reajuste" class="form-horizontal">

          <div class="form-group">
            <label class="col-sm-2 control-label" for="input-category">Departamento</label>
            <div class="col-sm-10">
              <select name="category_id" class="form-control" id="input-category">
                <option value="">Selecione...</option>
                <?php foreach ($categories as $category) { ?>
                <option value="<?php echo $category['category_id']; ?>"><?php echo $category['name']; ?></option>
                <?php } ?>
              </select>
              <?php if (isset($error['error_category'])) { ?>
              <span class="text-danger"><?php echo $error['error_category']; ?></span>
              <?php } ?>
            </div>
          </div>

          <div class="form-group">
            <label class="col-sm-2 control-label">
                <?php if ($opcao) { ?>
                <input type="radio" name="opcao" value="0">
                <?php } else { ?>
                <input type="radio" name="opcao" value="0" checked="checked">
                <?php } ?>
                Reajustar em porcentagem:
            </label>
            <div class="col-md-2 col-sm-3">
              <select name="sinal" class="form-control">
                <option value="+"<?php if ($sinal=='+') {echo ' selected="selected"';} ?>> + </option>
                <option value="-"<?php if ($sinal=='-') {echo ' selected="selected"';} ?>> - </option>
              </select>
            </div>
            <div class="col-md-8 col-sm-9">
              <div class="input-group">
                <input type="text" name="porcentagem" value="<?php echo $porcentagem; ?>" class="float form-control" />
                <span class="input-group-addon">%</span>
              </div>
              <?php if (isset($error['error_porcentagem'])) { ?>
              <span class="text-danger"><?php echo $error['error_porcentagem']; ?></span>
              <?php } ?>
            </div>
          </div>

          <div class="form-group">
            <label class="col-sm-2 control-label">
                <?php if ($opcao) { ?>
                <input type="radio" name="opcao" value="1" checked="checked">
                <?php } else { ?>
                <input type="radio" name="opcao" value="1">
                <?php } ?>
                Alterar para preço fixo:
            </label>
            <div class="col-sm-10">
              <div class="input-group">
                <span class="input-group-addon">R$</span>
                <input type="text" name="price" value="<?php echo $price; ?>" class="float form-control" />
              </div>
              <?php if (isset($error['error_price'])) { ?>
              <span class="text-danger"><?php echo $error['error_price']; ?></span>
              <?php } ?>
            </div>
          </div>

          <div class="form-group">
            <label class="col-sm-2 control-label" for="input-category">&nbsp;</label>
            <div class="col-sm-10">
              <a class="btn btn-info" onclick="visualizar();"><i class="fa fa-eye"></i> Visualizar alterações</a>
            </div>
          </div>

          <table id="produtos" style="display:none;" class="table table-bordered table-hover">
            <thead>
              <tr>
                <td class="text-left">Produto</td>
                <td class="text-right">Preço atual</td>
                <td class="text-right">Novo preço</td>
              </tr>
            </thead>
            <tbody>
            </tbody>
          </table>
          <p id="texto" style="text-align:right;"></p>

        </form>
      </div>
    </div>
  </div>
</div>
<script type="text/javascript"><!--
function visualizar() {
  $.ajax({
    url: 'index.php?route=extension/module/reajuste/produtos&token=<?php echo $token; ?>',
    data: $('#form-reajuste input[type="text"], #form-reajuste input[type="radio"]:checked, #form-reajuste select'),
    type: 'post',
    dataType: 'json',
    success: function(json) {
      console.log(json);
      if (json.mensagem) {
        window.alert(json.mensagem);
      }
      if (json.produtos) {
        $('#produtos').show();
        removeProducts();
        var i;
        for (i = 0; i < json.produtos.length; i++) {
          addProduct(json.produtos[i].name, json.produtos[i].price, json.produtos[i].new_price);
        }
        $('#texto').text('Para aplicar as alterações, clique no botão Salvar, no canto superior direito da tela.').show();
      } else {
        $('#produtos, #texto').hide();
      }
    }
  });
}
function removeProducts() {
  $('#produtos tbody').html('');
}
function addProduct(name, price, new_price) {
  html = '<tr>';
  html += '  <td class="text-left">' + name + '</td>';
  html += '  <td class="text-right">' + price + '</td>';
  html += '  <td class="text-right">' + new_price + '</td>';
  html += '</tr>';

  $('#produtos tbody').append(html);
}
//--></script>
<script type="text/javascript"><!--
function prepararFloat(el) {
  el.value = el.value.replace(/\,/g, '.');
  var posDecimal = el.value.lastIndexOf('.');
  if (posDecimal > 0) {
    var r = el.value.substr(0, posDecimal);
    r = r.replace(/\./g, '');
    r += el.value.substr(posDecimal);
    el.value = r;
  }
}
$('input.float').on('blur', function() {prepararFloat(this);});
//--></script>
<?php echo $footer; ?>