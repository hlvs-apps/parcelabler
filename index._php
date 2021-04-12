<?php
/*
 * Copyright 2012 Dallas Gutauckis 
 * 
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * 
 *     http://www.apache.org/licenses/LICENSE-2.0
 * 
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

/**
 * Parcelabler
 *
 * An Android parcelabler creator
 *
 * @since 2012-02-22
 * @author Dallas Gutauckis <dallas@gutauckis.com>
 */

// Basic autoloader
function autoload( $class ) {
  $path = 'include/' . $class . '.php';

  if ( file_exists( $path ) ) {
    require( $path );
  }
}

spl_autoload_register('autoload');

$file = '';
if ( isset( $_POST['file'] ) )
{
  $file = trim( $_POST['file'] );
}

$postedFields = isset( $_POST['fields'] ) ? explode( ',', $_POST['fields'] ) : array();

?>
<html>
<head>
  <link rel="stylesheet" href="stylesheets/bootstrap.min.css" type="text/css" charset="utf-8" />
  <script type="text/javascript" src="javascripts/jquery.js"></script>
  <script type="text/javascript" src="javascripts/jquery-ui.js"></script>
  <title>parcelabler</title>
</head>
<body style="width: 100%">

<script>
function fallbackCopyTextToClipboard(text) {
          var textArea = document.createElement("textarea");
          textArea.value = text;

          // Avoid scrolling to bottom
          textArea.style.top = "0";
          textArea.style.left = "0";
          textArea.style.position = "fixed";

          document.body.appendChild(textArea);
          textArea.focus();
          textArea.select();

          try {
            var successful = document.execCommand('copy');
            var msg = successful ? 'successful' : 'unsuccessful';
            console.log('Fallback: Copying text command was ' + msg);
          } catch (err) {
            console.error('Fallback: Oops, unable to copy', err);
          }

          document.body.removeChild(textArea);
        }
        function copyTextToClipboard(text) {
          if (!navigator.clipboard) {
            fallbackCopyTextToClipboard(text);
            return;
          }
          navigator.clipboard.writeText(text).then(function() {
            console.log('Async: Copying to clipboard was successful!');
          }, function(err) {
            console.error('Async: Could not copy text: ', err);
          });
        }
</script>
<a href="https://github.com/hlvs-apps/parcelabler"><img style="position: absolute; top: 0; right: 0; border: 0;" src="assets/forkme_right_orange_ff7600.png" alt="See me on GitHub"></a>
  <div class="container">
  <div class="content">
    <h1>parcelabler</h1>
    <h6>for Android Parcelable implementations</h6>

    <form method="POST">
      <fieldset>
        <div class="row">
          <div class="span10">
            <h3>Code</h3>
            <textarea name="file" rows="20" class="span10"><?php echo htmlentities( $file ); ?></textarea>
            <span class="help-block">Paste your full class definition into the box above to get the Parcelable implementation and options for removing fields for parceling.</span>
          </div>
          <div class="span6">
            <h3>Fields</h3>
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
       . '<input type="checkbox" ' . ( $isChecked ? 'checked="checked"' : '' ) . 'name="field[' . $fieldName . ']" />'
       . '<span>' . $fieldName . '</span></label></li>';

      $_POST['field'][$fieldName] = $isChecked;
    } else {
      array_push( $unsupportedFields, $field );
    }
  }
}

echo '</ul>';

$unrecognizedFields = $codeBuilder->getUnrecognizedFields();
if ($file && $codeBuilder->getClass() && !empty($unrecognizedFields)) {
  echo '<br/><div class="alert-message error">The following variables were not recognized (syntax error?):
  <br/><br/><ol>';
  
  foreach ($unrecognizedFields as $variable) {
    echo '<li class="error-item">' . $variable . '</li>';
  }

  echo '</ol></div>';
}

if ($file && $codeBuilder->getClass() && !empty($unsupportedFields)) {
  echo '<br/><div class="alert-message error">The following variables cannot be written to Parcel:
  <br/><br/><ol>';
  
  foreach ($unsupportedFields as $variable) {
    echo '<li class="error-item">' . $variable->getType() . ' ' . $variable->getName() . ';</li>';
  }

  echo '</ol></div>';
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
    echo '<br/><div class="alert-message warning">Check that the following classes implement either Parcelable or Serializable.<br/>'
    . 'Otherwise you\'ll get a RuntimeException.<br/><br/><ol>';
    
    foreach ($suspiciousTypes as $type) {
      echo '<li>' . $type . '</li>';
    }

    echo '</ol></div>';
  }
}

echo '<input type="hidden" name="fields" value="' . implode( ',', array_keys( $codeBuilder->getFields() ) ) . '" />
</div>
</div>
</fieldset>
<div class="actions">
  <input type="submit" name="submit" class="btn primary" value="Build" />
</div>
</form>';

if ( isset( $_POST['submit'] ) && ! $codeBuilder->getClass() ) {
  echo '<div class="alert-message info"><strong>Try again...</strong> We really do need the full class definition (including the class name) to build this. I promise, we won\'t steal your code.</div>';
} else if ( isset( $_POST['submit'] ) ) {
  $selectedFields = $_POST['field'];

  if ( count( $selectedFields ) > 0 ) {
    $code = htmlentities( $codeBuilder->getOutput( $selectedFields ) );

    echo '<h3>Output</h3><div class="alert-message success"><strong>Great news!</strong> Your code was parsed, you had fields for parceling, and the implementation for Parcelable is below.</div><p>Add the <a href="http://developer.android.com/reference/android/os/Parcelable.html">Parcelable</a> class to yours and add the following methods.</p><pre >' . $code . '</pre>';
    ?>
    <div style="display:inline-block; vertical-align:top;">
      <button class="js-copy-code-btn">Copy to Clipboard</button><br/><br />
    </div>

    <script>
    var copyCodeBtn = document.querySelector('.js-copy-code-btn');

    copyCodeBtn.addEventListener('click', function(event) {
      copyTextToClipboard(`<?php echo $code ?>`);
    });
    </script>
    <?php
  } else {
    echo '<div class="alert-message error"> It looks like you don\'t have anything for parceling.</div>';
  }
}

?>
  </div>
  </div>
</body>
</html>
