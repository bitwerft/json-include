# JSON Include
This allows you to use `#include` within `.json` files, so you can break up large json files into multiple smaller ones.

## Installation
**Through [composer](https://getcomposer.org/):**
- Simply execute `composer require bitwerft/jsoninclude`

## Usage
**1. JSON File**
- In your json file add a "#include" as the key, with the name (if in the same folder) or the path to the file as the value
- This can be in the middle of an array, as the only array element or nested deep in the file. The content of the included json will be placed at the exact same place.
- This file needs to be a valid json
- There is a maximum Number of includes which is by default set to 100 to prevent circular includes
- **Execute using `JsonInclude::render('filename.json');`**

**2. Included JSON Files**
- The included files need to be valid json themselves
- They don't need to have the `.json` file extension
- Every file can be included multiple times
- They can also have "#include"s, which will be included recursivly
- Circular includes will cause an error

**3. Result**
- The assembled json will be returned as an unformatted string
- By default if any include fails, an Exception containing all info will be thrown
- The last used info array can also be accesed through `JsonInlude::$info`



## Example

1. JSON File

```
{
    "text": [
        {"#include":"http://example.com/file.json"}
    ],
    
    "#include":"file:///storage/app/file2.json"
}
```

2. JSON Include 1 (http://example.com/file.json)
```
{
    "paragraph1": {
        "sentence": "Lorem ipsum"
    },
    "paragraph2": {
        "sentence": "dolor sit amet"
    }
}
```
2. JSON Include 2 (file:///storage/app/file2.json)
```
{
"ids": [1, 2, 3, 4, 5]
}
```

3. Result (with added formatting)
```
{
    "text":[
        {
            "paragraph1": {
                "sentence":"Lorem ipsum"
            },

            "paragraph2": {
                "sentence":"dolor sit amet"
            }
        }
    ],
    "ids": [1,2,3,4,5]
}
```
