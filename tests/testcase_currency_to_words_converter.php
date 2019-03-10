<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../library/class_currency_to_words_converter.php';

class CurrencyToWordsConverterTest extends TestCase 
{
  function testConvert1() 
  {
    $expected1 = "One Crore, Thirty-eight Lakh, Three Thousand and Thirty rupees and One paisa only";

    $test_value1 = 13803030.01;
    $result1 = CurrencyToWordsConverter::convertToWords( $test_value1 );

    $this->assertEquals( $result1, $expected1 );
  }

  function testConvertPaiseOnly() 
  {
    $expected = "Twenty-three paise only";

    $test_value = ".23";
    $result = CurrencyToWordsConverter::convertToWords( $test_value );

    $this->assertEquals( $result, $expected );
  }

  function testConvertUnits() 
  {
    $expected = "One rupee and Twenty-three paise only";

    $test_value = "1.23";
    $result = CurrencyToWordsConverter::convertToWords( $test_value );

    $this->assertEquals( $result, $expected );
  }

  function testConvertTens() 
  {
    $expected = "Thirty rupees and Twenty-three paise only";

    $test_value = "30.23";
    $result = CurrencyToWordsConverter::convertToWords( $test_value );

    $this->assertEquals( $result, $expected );
  }

  function testConvertHundreds() 
  {
    $expected = "Four Hundred and Sixty-four rupees and Twenty-three paise only";

    $test_value = "464.23";
    $result = CurrencyToWordsConverter::convertToWords( $test_value );

    $this->assertEquals( $result, $expected );
  }

  function testConvertThousands() 
  {
    $expected = "Seven Thousand, Nine Hundred and Eighty-three rupees and Twenty-three paise only";

    $test_value = "7983.23";
    $result = CurrencyToWordsConverter::convertToWords( $test_value );

    $this->assertEquals( $result, $expected );
  }

  function testConvertSeveralThousands() 
  {
    $expected = "Sixty-seven Thousand, Nine Hundred and Eighty-three rupees and Twenty-three paise only";

    $test_value = "67983.23";
    $result = CurrencyToWordsConverter::convertToWords( $test_value );

    $this->assertEquals( $result, $expected );
  }

  function testConvertSeveralThousandsWithoutAnd() 
  {
    $expected = "Sixty-seven Thousand, Nine Hundred, Eighty-three rupees, Twenty-three paise only";

    $test_value = "67983.23";
    $result = CurrencyToWordsConverter::convertToWords( $test_value, array( 'hundreds_separator' => ', ' ) );

    $this->assertEquals( $result, $expected );
  }
}
