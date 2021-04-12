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

$codeBuilder = new CodeBuilder( $file );

$errorAndWarnings="";
$code="";
$supportedFields=array();

$unsupportedFields = array();
if ( $file && $codeBuilder->getClass() ) {
  $fields = $codeBuilder->getFields();

  foreach ( $fields as $field ) {
    $fieldName = $field->getName();
    $supportLevel = $codeBuilder->getSupportLevel( $field );

    if ( $supportLevel != SupportLevel::Unsupported ) {
       array_push($supportedFields, $field=true);
    } else {
       array_push( $unsupportedFields, $field );
    }
  }
}

$unrecognizedFields = $codeBuilder->getUnrecognizedFields();
if ($file && $codeBuilder->getClass() && !empty($unrecognizedFields)) {
  $errorAndWarnings=$errorAndWarnings . '<div class="alert-message error">The following variables were not recognized (syntax error?):
  <br/><br/><ol>';

  foreach ($unrecognizedFields as $variable) {
    $errorAndWarnings=$errorAndWarnings . '<li class="error-item">' . $variable . '</li>';
  }

  $errorAndWarnings=$errorAndWarnings . '</ol></div>';
}

if ($file && $codeBuilder->getClass() && !empty($unsupportedFields)) {
  $errorAndWarnings=$errorAndWarnings . '<br/><div class="alert-message error">The following variables cannot be written to Parcel:
  <br/><br/><ol>';

  foreach ($unsupportedFields as $variable) {
    $errorAndWarnings=$errorAndWarnings . '<li class="error-item">' . $variable->getType() . ' ' . $variable->getName() . ';</li>';
  }

  $errorAndWarnings=$errorAndWarnings . '</ol></div>';
}

if ($file && $codeBuilder->getClass()) {
  $suspiciousTypes = array();
  $fields = $codeBuilder->getFields();

  foreach ( $fields as $field ) {
    if ( $codeBuilder->getSupportLevel( $field ) != SupportLevel::Unsupported ){
      if ( $codeBuilder->isTypeUnconditionallyParcelable($field->getType()) === false ) {
        array_push( $suspiciousTypes, $field->getType() );
      }
      $typeParam = $field->getTypeParam();
      if ( !empty($typeParam) && $codeBuilder->isTypeUnconditionallyParcelable($typeParam) === false ) {
        array_push( $suspiciousTypes, htmlentities($typeParam) );

      }
    }
  }

  if (!empty($suspiciousTypes)) {
    $errorAndWarnings=$errorAndWarnings . '<br/><div class="alert-message warning">Check that the following classes implement either Parcelable or Serializable.<br/>'
    . 'Otherwise you\'ll get a RuntimeException.<br/><br/><ol>';

    foreach ($suspiciousTypes as $type) {
      $errorAndWarnings=$errorAndWarnings . '<li>' . $type . '</li>';
    }

    $errorAndWarnings=$errorAndWarnings . '</ol></div>';
  }
}


if (! $codeBuilder->getClass() ) {
  $errorAndWarnings=$errorAndWarnings . '<div class="alert-message info"><strong>Try again...</strong> We really do need the full class definition (including the class name) to build this.</div>';
} else{
  if ( count( $supportedFields ) > 0 ) {
    $code = htmlentities( $codeBuilder->getOutput( $supportedFields ) );
    $code='<pre> <code class="java">' . $code . '</code></pre>';
    //echo '<h3>Output</h3><div class="alert-message success"><strong>Great news!</strong> Your code was parsed, you had fields for parceling, and the implementation for Parcelable is below.</div><p>Add the <a href="http://developer.android.com/reference/android/os/Parcelable.html">Parcelable</a> class to yours and add the following methods.</p><pre >' . $code . '</pre>';
  } else {
    $code='empty';
    $errorAndWarnings=$errorAndWarnings . '<div class="alert-message error"> It looks like you don\'t have anything for parceling.</div>';
  }
}
$returnData=["error"=>$errorAndWarnings,"build" => $code];
$json = json_encode($returnData);
echo $json;
?>
