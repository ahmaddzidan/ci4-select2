# Select2 library for Codeigniter 4
PHP Library to handle server-side processing for Select2, in a fast and simple way.

## Installation
Drop the contents of the download zip into your application library directory, then load library on your controller file. That's it. 

## How to use it?
In your controller file, add this line to load library:

```php
<?php
  namespace App\Controllers;

  use App\Libraries\Select2;

  class Example extends BaseController {}
```
on your controller method:

```php
  ...
    public function ajax_select2()
    {
      $options = array(
        'table'=> 'table_name', // name of your table
        'id' => 'id', // id of your table field and will be assigned to value attribute of select option <option value="{id}">{text}/option>
        'text' => 'text', // text of your table field and will be assigned to text of select option
        'additional'=> ['field1','field2'..] // additional data will be assigned to data-* attribute <option data-field="{field}" value="{id}">{text}/option> 
      );

      $select2 = new Select2($options);

      echo json_encode($select2->render());
    }
  
```
and  on your js file :
```js
    var _componentSelect2 = function() {
        if (!$().select2) {
            console.warn('Warning - select2.min.js is not loaded.');
            return;
        }
        
        $('.select2-ajax').each(function(e) {
            let elm = $(this),
                options = {
                    allowClear: true,
                    ajax: {
                        url: elm.data('url'),
                        dataType: 'json',
                        delay: 250,
                        data: function(params){
                            return $.extend({}, params, {
                                term: params.term || '',
                                page: params.page || 1,
                                filter : elm.attr('data-filter')
                            })
                        },
                        cache: true,
                    },
                    templateSelection: function (data, container) {
                        // Add custom attributes to the <option> tag for the selected option
                        if (data.additional !== undefined) {
                            for (var i = data.additional.length - 1; i >= 0; i--) {
                                for (const [key, value] of Object.entries(data.additional[i])) {
                                    $(data.element).attr('data-'+ key, value);
                                }
                            }
                        }

                        return data.text;
                    }
                };

                if (elm.data('tags') == 'true') {
                    options.tags = true;
                    options.tokenSeparators = [',', ' '];
                }

                $(elm).select2(options);
        });
    }
```
and then add this line to your html file :
```html
<select class="select2-ajax" data-url="{base_url('example/ajax_select2')}" data-fouc data-placeholder="-- choose -- ">
  <option></option>
</select>
```
**NOTE:** Don't forget to load jquery select2 plugin (js & css) to your html document. 

## Supported versions of Codeigniter
Select2 Library for Codeigniter 4 has been tested and working on the latest version of Codeigniter (v4.1.3). this not supported for CI 3 anymore and you should be using 4.0

## Requirements
Codeigniter > 4.0  
Select2 > 4.0.13 
PHP > 7.1.3

## License
Copyright (c) 2021 Ahmad Sanusi, released under [the MIT license](https://github.com/ahmaddzidan/ci4-select2/blob/master/LICENCE)
