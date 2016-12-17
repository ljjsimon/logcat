##files
There is a demo in plugin/map. Logcat puts all plugins in plugin/ like that. And this introduction takes the map plugin as example.

##frontend
The main part of Logcat frontend relaies on [jQuery](jquery.com) [echarts](https://github.com/ecomfe/echarts) [vue](https://github.com/vuejs/vue) [element](https://github.com/ElemeFE/element). So all these files are preloaded. However, you don't have to use them. Just include anything you need in your html.

##backend
Your main PHP class should be named the same as your plugin name with the first letter in upper case. And it should extend from class Log which is in lib/Log.php.  
There are functions you can overwrite in your main class:
- getHtml() where you read your html file and return it
- filterInput() where you can filter $_GET and $_POST
- beforeGet() where you can initialize your result array
- getFields($fields) where you get an array combined one line of log with the name of every part of it
- got() where you can deal with the result array, and you munst return it

##config
add the information of your plugin to the configuration in config.json