![微擎](http://bbs.we7.cc/static/image/common/logo.png)

###微擎开源微信公众号管理系统

感谢您选择微擎系统。

微擎是一款免费开源的微信公众号管理系统，基于目前最流行的WEB2.0架构（php+mysql），支持在线升级和安装模块及模板，拥有良好的开发框架、成熟稳定的技术解决方案、活跃的第三方开发者及开发团队，依托微擎开放的生态系统，提供丰富的扩展功能。

###运行环境
IIS/Apache/Nginx、PHP >=5.3、MySQL>=5.0
运行微擎系统必须保证环境版本满足上述要求，具体环境检测可以运行 _install.php_ 文件进行检测。

###安装
请保您将微擎程序文件放置在您的网站目录中，微擎项目目录结构如下：
```
    addons             微擎模块
    api                对接外部系统接口
    app                微站 （Mobile / App）
    attachment         附件目录
    framework          微擎框架
    payment            支付调用目录
    tester             测试用例
    upgrade            升级脚本
    web                后台管理
    api.php            微信api接口
    index.php          系统入口
    install.php        安装文件
    password.php       密码重置
```
请运行目录中的 _install.php_ 文件，根据提示完成安装。

