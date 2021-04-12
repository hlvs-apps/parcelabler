<?php

// Basic autoloader
function autoload( $class ) {
  $path = 'include/' . $class . '.php';

  if ( file_exists( $path ) ) {
    require( $path );
  }
}

spl_autoload_register('autoload');

$_POST = json_decode(file_get_contents('php://input'), true);

$file = '';
if ( isset( $_POST['file'] ) )
{
  $file = trim( $_POST['file'] );
}

$postedFields = isset( $_POST['fields'] ) ? explode( ',', $_POST['fields'] ) : array();
?>
<ul class="inputs-list">
<?php

$codeBuilder = new CodeBuilder( $file );

$unsupportedFields = array();
if ( $file && $codeBuilder->getClass() ) {
  $fields = $codeBuilder->getFields();

  foreach ( $fields as $field ) {
    $fieldName = $field->getName();
    $isChecked = '';
    $supportLevel = $codeBuilder->getSupportLevel( $field );

    if ( $supportLevel != SupportLevel::Unsupported ) {
      $isChecked = count( $postedFields ) == 0 || false === in_array( $fieldName, $postedFields ) || ( isset( $_POST['field'][$fieldName] ) && !!$_POST['field'][$fieldName] );

      echo '<li>'
       . '<label>'
       . '<span>' . $fieldName . '</span></label></li>';

    } else {
      array_push( $unsupportedFields, $field );
    }
  }
}

echo '</ul>';
?>
