<?php

/**
 * A class to convert a currency value into words.
 *
 * This uses the Indian locale format for grouping the numbers. This means using lakhs and
 * crores as the next highest group after thousands.
 *
 * @package Util
 * @author  Ravi Damarla <ravi.damarla@gmail.com>
 * @version $Id$
 * @access  public
 */
class CurrencyToWordsConverter {

  /*
   * The list of words for the common numbers.
   */
  private static $currency_words = array(
    0 => '',
    1 => 'One',
    2 => 'Two',
    3 => 'Three',
    4 => 'Four',

    5 => 'Five',
    6 => 'Six',
    7 => 'Seven',
    8 => 'Eight',
    9 => 'Nine',

    10 => 'Ten',
    11 => 'Eleven',
    12 => 'Twelve',
    13 => 'Thirteen',
    14 => 'Fourteen',
    
    15 => 'Fifteen',
    16 => 'Sixteen',
    17 => 'Seventeen',
    18 => 'Eighteen',
    19 => 'Nineteen',

    20 => 'Twenty',
    30 => 'Thirty',
    40 => 'Forty',
    50 => 'Fifty',
    60 => 'Sixty',

    70 => 'Seventy',
    80 => 'Eighty',
    90 => 'Ninety'
  );

  /*
   * Grouping of values in the Indian locale format.
   */
  private static $currency_grouping = array( 
    'Hundred',
    'Thousand',
    'Lakh',
    'Crore'
  );

  /**
   * Returns the currency value in words.
   * 
   * @param float $value the value to convert
   * @param array $options options to configure the conversion
   *
   * @return string the formatted value
   */
  public static function convertToWords( float $value, $options = array( 'hundreds_separator' => ' and ' ) ) {

    if ( $value < 0 ) {
      return "";
    }

    // Use the Indian locale.
    setlocale( LC_MONETARY, 'en_IN' );

    // Format the value using the Indian currency format so that we can split into groups using the separators.
    //  ! to suppress the currency symbol
    //  n to format according to the locale's national currency format.
    $formatted_currency = money_format( '%!n', $value );


    // Separate the whole and decimal parts on the '.'.
    $curr_parts = explode( '.', $formatted_currency );
    $whole_part = $curr_parts[0];
    $decimal_part = $curr_parts[1];

    // Separate the groups in the whole part.
    $whole_values = explode( ',', $whole_part );

    $str_values = array();
    $group_count = count( $whole_values );

    // Process each of the groups.
    for( $ix = 0; $ix < $group_count; $ix++ ) {

      // Calculate the position of this group.
      $position = $group_count - $ix - 1;
      $str_values[] = self::getWordsForGroup( $whole_values[ $ix ], $position, $options );
    }

    $hundreds_words = array_pop( $str_values );

    $hundreds_str = "";
    if ( !empty ( $hundreds_words ) ) {
      if ( count( $str_values ) == 1 ) {
        $hundreds_str = ", ";
      } else if ( count( $str_values ) > 1 ) {
        $hundreds_str = $options['hundreds_separator'];
      }
      $hundreds_str .= $hundreds_words;
    }

    $decimal_words = null;
    if ( $decimal_part > 0 ) {
      // Add the 'and' only if there are lakhs.
      if ( !empty( $hundreds_words ) ) {
        $decimal_words = $options['hundreds_separator'];
      }
      $decimal_words .= self::getWordsForGroup( $decimal_part, -1, $options  );
    }

    $words_str = implode( ', ', $str_values );

    if ( isset( $hundreds_str ) ) {
      $words_str .= $hundreds_str;
    }

    if ( isset( $decimal_words ) ) {
      $words_str .= $decimal_words;
    }

    return $words_str . " only";
  }

  /*
   * Processes a single group (crores, lakhs, thousands, etc.) and converts them into words.
   *
   * @param float $group_value the value of the group
   * @param int   $position the position of the group: hundreds, thousands, lakhs, crores
   * @param array $options the configuration options.
   */
  private static function getWordsForGroup( $group_value, $position, $options ) {

    $ret_str = "";

    // Ensure that this is an integer.
    $int_value = intval( $group_value );

    // Don't process negative values.
    if ( $int_value <= 0 ) {
      return $ret_str;
    }

    switch ( $position ) {
      case -1:
        // Process the paise value.
        $ret_str = self::getWordsForNumber( $int_value, $position, $options );

        // Use plural if value is greater than one.
        $ret_str .= " " . ( ( $int_value == 1 ) ? "paisa" : "paise" );
        break;

      case 0:
        // Process the Hundreds value.
        $ret_str = self::getWordsForNumber( $int_value, $position, $options );

        // Use plural if value is greater than one.
        if ( $int_value == 1 ) {
          $ret_str .= ' rupee';
        } else {
          $ret_str .= ' rupees';
        }
        break;

      case 1:
      case 2:
        // Thousands and Lakhs - these will always be less than 100.
        $ret_str = self::getWordsForNumber( $int_value, $position, $options ) . " " . self::$currency_grouping[ $position ];
        break;

      default:
        // Crores
        $ret_str = self::getWordsForNumber( $int_value, $position, $options ) . " " . self::$currency_grouping[ min( $position, 3 ) ];
        break;
    }

    return $ret_str;
  }

  /*
   * Build the words for a single number.
   *
   * @param float $number the number to convert
   * @param int   $position the position (hundreds, lakhs, etc.) of this number
   * @param array $options the configuration options.
   *
   * @return string the words for the group
   */
  private static function getWordsForNumber( $number, $position, $options ) {

    $ret_str = "";

    // Don't process negative numbers for now.
    if ( $number < 0 ) {
      return "";
    }
    
    if ( $number < 21 ) {

      // If less than 21, do a simple lookup for the single word.
      // If more than 21, will need to combine two separate words.
      return self::$currency_words[ $number ];

    } else {

      // Split into hundreds, tens and units so that each place can be converted into a separate word.
      $groups = self::getNumberGroups( $number );

      // Process the hundreds value if greater than zero.
      if ( $groups[2] > 0 ) {
        
        // 200 becomes "Two Hundred".
        $ret_str = self::$currency_words[ $groups[2] ] . " " . self::$currency_grouping[ $position ];
      }

      // Process the tends value only if greater than zero.
      if ( $groups[1] > 0 ) {

        if ( $groups[2] > 0 && $position == 0 ) {
          // If there is a hundreds value, add a separator obtained from the options.
          // 231 becomes "Two Hundred (,|and) Thirty-one"
          $ret_str .= $options['hundreds_separator'];
        }

        // Append the tens value.
        $ret_str .= self::$currency_words[ $groups[1] ];
      }

      // Process the units value if greater than zero.
      if ( $groups[0] > 0 ) {

        $units_str = self::$currency_words[ $groups[0] ];

        if ( $groups[1] > 0 ) {
          // If there is a tens value add a '-' in between the tens and units words.
          // e.g. 231 becomes Two Hundred and Thirty-one
          $ret_str .= "-";
          $ret_str .= strtolower($units_str);
        } else {
          // If there is no tens value, use the capital word for units.
          // e.g 201 becomes Two Hundred and One
          $ret_str .= $units_str;
        }
      }
    }

    return $ret_str;
  }

  /**
   * Split the number into hundreds, tens and units.
   * 
   * If the value 283 is passed, this method will return the array:
   * ( 3, 8, 2 );
   * 
   * @param float $number the number to process
   * @return array the number split into individual values
   */
  private static function getNumberGroups( $number ) {
    
    // Default values.
    $units_value = 0;
    $tens_value = 0;
    $hundreds_value = 0;

    $hundreds_rem = $number;

    if ( $number > 100 ) {
      $hundreds_value = intval( $number / 100 );
      $hundreds_rem = $number % 100;
    }

    if ( $hundreds_rem > 10 ) {
      $units_value = $hundreds_rem % 10;
      $tens_value = intval( $hundreds_rem / 10 ) * 10;
    } else {
      $units_value = $hundreds_rem;
    }

    return array( $units_value, $tens_value, $hundreds_value );
  }
}
