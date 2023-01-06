#!/bin/bash
# bash启动服务脚本，脚本允许做一些基础服务管理
# 脚本文件位置不可移动，允许非脚本目录下运行

PHP_PATH=php
ACTION_NAME=""
ENV_NAME=""
SHOW_HELP='0'
IS_REMEMBER='0'
# 参数处理
for ITEM;do
    if [ "$ITEM" == '-h' -o "$ITEM" == '--help' ];then
        SHOW_HELP='1'
    elif [ "$ITEM" == '-r' ];then
        IS_REMEMBER='1'
    elif [ -z "$ACTION_NAME" ];then
        if [[ "$ITEM" =~ ^(start|stop|restart|reload|status|connections)$ ]];then
            ACTION_NAME="$ITEM"
        else
            echo "请指定正确操作名： $ITEM"
            exit 1
        fi
    elif [ -z "$ENV_NAME" ];then
        if [[ "$ITEM" =~ ^(local|test|preview|production)$ ]];then
            ENV_NAME="$ITEM"
        else
            echo "请指定环境操作名： $ITEM"
            exit 1
        fi
    else
        echo "未知选项参数：$ITEM"
    fi
done
if [ $# = '0' -o $SHOW_HELP = '1' ];then
    echo "
linux系统下服务管理脚本，用来快速启动指定环境下的服务。

Command:
    bash $0 [Arguments] [Options]
Arguments:
    action      服务动作，主要有：
                    start       开启服务，所有服务开启
                    stop        强制停止服务，所有服务停止
                    restart     强制重启服务，重新启动所有服务
                    reload      平滑重启服务，重新启动所有服务
                    status      查看服务状态
                    connections 查看服务连接信息
    env         服务运行环境配置，主要有：
                    local       本地环境
                    test        测试环境
                    preview     预发环境
                    production  生产环境
Options:
    -h, --help  查看帮助脚本信息
    -r          记录环境名
"
    exit
fi

# 获取根目录
SERVER_BASH_PATH=$(cd "$(dirname "${BASH_SOURCE[0]}")/"; pwd)

# 记录当前环境名
if [ $IS_REMEMBER = '1' -a -n "$ENV_NAME" ];then
    echo "$ENV_NAME" > $SERVER_BASH_PATH/.server-env
fi
# 回填上次环境名
if [ -e "$SERVER_BASH_PATH/.server-env" -a -z "$ENV_NAME" ];then
    ENV_NAME=$(cat $SERVER_BASH_PATH/.server-env)
fi
# 参数验证
if [ -z "$ACTION_NAME" ];then
    echo "请指定操作名"
    exit 1
fi
if [ -z "$ENV_NAME" ];then
    echo "请指定环境名"
    exit 1
fi
# 执行操作
if [[ $ACTION_NAME =~ ^(start|restart)$ ]];then
    nohup $PHP_PATH $SERVER_BASH_PATH/start.php $ACTION_NAME --env=$ENV_NAME 2>&1 >$SERVER_BASH_PATH/logs/$(date '+%Y-%m-%d-%H-%M-%S').log &
    bash $0 status $ENV_NAME
else
    $PHP_PATH $SERVER_BASH_PATH/start.php $ACTION_NAME --env=$ENV_NAME
fi
