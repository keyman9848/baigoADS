## 广告脚本概述

请注意区分此处的广告脚本插件和插件和之间的区别，广告脚本插件一般指 JavaScript 开发的 jQuery 插件，属于客户端脚本。而插件是指系统级的，一般用 PHP 语言开发，属于服务器端程序。

本系统的内置的常用广告脚本均基于 jQuery 库，少数基于 Bootstrap，推荐开发者使用 jQuery 开发。

----------

#### 目录

根据脚本的名字，创建一个目录，目录必须由英文、数字组成，要注意重名的问题。

广告脚本存放于 `./public/advert/` 目录，一个脚本一个目录，所有目录、文件名必须使用英文名。

---------- 
 
#### 文件组成

目录中至少应当包含一个主文件，文件名必须以 `.min.js` 为后缀，文件名可以和目录同名，也可以在描述文件中定义。

根据需要，也可以把主文件拆分成多个文件，自行载入。

插件中可以包含一个描述文件，文件名必须为 `config.json`；

还可以包含选项文件 `opts.json`。

另外还可以增加 CSS、图片文件等。

| 名称 | 描述 |
| - | - |
| 名称.min.js（必需） | 主文件 |
| config.json | 描述文件 |
| opts.json | 选项文件 |
| readme.txt | 说明文档 |
| 名称.css | 样式文件，css 格式。 |
| 其他文件 | ... |
