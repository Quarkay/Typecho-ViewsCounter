Typecho-ViewsCounter
----

## 简介

ViewsCounter 插件用于记录 Typecho 每篇文章的浏览次数（可设置对同一篇文章的多次浏览行为是否计入浏览量的时间间隔），同时提供获取浏览最多文章的功能。使用时需要与 Typecho 主题的代码配合。

## 安装

1. 首先将本项目克隆到本地：

    ```bash
    git clone git@github.com:Quarkay/Typecho-ViewsCounter.git
    ```

2. 将子文件夹 ViewsCounter 复制到 Typecho 插件目录

    ```bash
    cp -r Typecho-ViewsCounter/ViewsCounter /path...to...your...typecho/usr/plugins/
    ```
    
3. 在Typecho后台点击启用并进行相关设置如下图：

    <img src="https://raw.githubusercontent.com/Quarkay/Typecho-ViewsCounter/master/config_example.png" alt="配置设置例子">

## 主题调用插件方法

直接**在需要显示的地方插入相应的调用代码**即可

1. 显示文章浏览次数 ( ?? Views 的效果)

    ```php
    // ... context ...
    <?php echo ViewsCounter_Plugin::getViews(); ?> Views
    // 也可以传入 cid 使用如下
    <?php echo ViewsCounter_Plugin::getViewsById($this->cid); ?> Views
    // ... context ...
    ```

2. 获取最多浏览量文章

    ```php
    // ... context ...
    <?php foreach (ViewsCounter_Plugin::getMostViewed() as $post): ?>
        <h3><a href="<?php echo $post['permalink'] ?>"><?php echo $post['title'] ?></a></h3>
        ...
        ...
    <?php endforeach; ?>
    // ... context ...
    ```
    具体可用字段参考如下：
    
    ```php
    array (size=1)
      0 => 
        array (size=32)
          'cid' => string '5' (length=1)
          'title' => string '文章标题' (length=8)
          'slug' => string '5' (length=1)
          'created' => string '1507556700' (length=10)
          'modified' => string '1512460496' (length=10)
          'text' => string '
                    关于串模式匹配算法，相信很多讲解数据结构的书籍都会有讲解，这里先大概提一下。
                    
                    <!--more-->
                    
                    ### 串模式匹配算法'(length=102)
          'order' => string '0' (length=1)
          'authorId' => string '1' (length=1)
          'template' => null
          'type' => string 'post' (length=4)
          'status' => string 'publish' (length=7)
          'password' => null
          'commentsNum' => string '0' (length=1)
          'allowComment' => string '1' (length=1)
          'allowPing' => string '1' (length=1)
          'allowFeed' => string '1' (length=1)
          'parent' => string '0' (length=1)
          'views' => string '240' (length=3)
          'categories' => 
            array (size=1)
              0 => 
                array (size=14)
                  ...
          'category' => string 'default' (length=7)
          'directory' => 
            array (size=1)
              0 => string 'default' (length=7)
          'date' => 
            object(Typecho_Date)[39]
              public 'timeStamp' => int 1507585500
          'year' => string '2017' (length=4)
          'month' => string '10' (length=2)
          'day' => string '09' (length=2)
          'hidden' => boolean false
          'pathinfo' => string '/archives/5/' (length=12)
          'permalink' => string 'http://127.0.0.1:8001/archives/5/' (length=33)
          'isMarkdown' => boolean true
          'feedUrl' => string 'http://127.0.0.1:8001/feed/archives/5/' (length=38)
          'feedRssUrl' => string 'http://127.0.0.1:8001/feed/rss/archives/5/' (length=42)
          'feedAtomUrl' => string 'http://127.0.0.1:8001/feed/atom/archives/5/' (length=43)
    ```
    
## License

<a href="https://github.com/Quarkay/Typecho-SimpleCDN/blob/master/LICENSE.txt">The GNU General Public License (GPL) V2</a>