<?php

namespace Bitwerft\JsonInclude;

class JsonInclude
{
    const KEYVALUE = "#include";
    const MAXINCLUDES = 100;
    const ALWAYSRETURN = false; // return a json string even if some includes failed

    public static $info = array('Errors' => 0, 'Max Includes' => self::MAXINCLUDES, 'Tried Includes' => 0);

    public static function render(string $inputJsonName) : string
    {
        // reset info array
        self::$info = array('Errors' => 0, 'Max Includes' => self::MAXINCLUDES, 'Tried Includes' => 0);

        // read and validate input JSON
        $json = file_get_contents($inputJsonName);
        if (!$json) throw new \Exception("Error: json file: " . $inputJsonName . " couldn't be opened");
        $json = json_decode($json, true);
        if (!$json) throw new \Exception("Error: json file: " . $inputJsonName . " isn't a valid json");

        // replace all includes
        $includeMightExist = true;
        $i = 0;
        do {
            $includeMightExist = self::findAndReplaceInclude($json);
            ++$i;
        } while ($includeMightExist && $i < self::MAXINCLUDES);

        if ($i >= self::MAXINCLUDES) ++self::$info['Errors'];

        // output the final result
        if (self::$info['Errors'] == 0 or self::ALWAYSRETURN)
        {
            return json_encode($json);        
        }
    throw new \Exception(json_encode(self::$info));
    }


    protected static function findAndReplaceInclude(array &$jsonArray) : bool
    {
        foreach ($jsonArray as $key => &$value)
        {
            if ($key === self::KEYVALUE)
            {
                $subJsonName = $value;
                $subJson = self::validateAndDecodeSubJson($subJsonName);     
                self::arraySpliceAssoc($jsonArray, $subJson);
                ++self::$info['Tried Includes'];
                return true;
            }
            if (is_array($value))
            {
                if (self::findAndReplaceInclude($value))
                {
                    return true;
                }
            }
        }
        return false;
    }


    protected static function validateAndDecodeSubJson($subJsonName) : array
    {
        $subJson = file_get_contents($subJsonName);
        if (!$subJson)
        {
            self::$info[$subJsonName] = "Couldn't be opened";
            $subJson = '{"#includeError": "' . $subJsonName . '"}';
            ++self::$info['Errors'];
        }

        $subJson = json_decode($subJson, true);
        if (!$subJson)
        {
            self::$info[$subJsonName] = "Isnt't a valid json";
            $subJson = json_decode('{"#includeError": "' . $subJsonName . '"}', true);
            ++self::$info['Errors'];
        }
        return $subJson;
    }


    // array_splice without loosing the keys of associative array
    protected static function arraySpliceAssoc(array &$jsonArray, array $subJson) : void
    {
        $offset = array_search(self::KEYVALUE, array_keys($jsonArray));
        $jsonArray = array_slice($jsonArray, 0, $offset, TRUE)
        + $subJson
        + array_slice($jsonArray, $offset + 1, NULL, TRUE);
        return;
    }
}
