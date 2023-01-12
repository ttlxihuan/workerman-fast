#!/bin/bash
# bash启动服务脚本，脚本允许做一些基础服务管理
# 脚本文件位置不可移动，允许非脚本目录下运行

PHP_PATH=php
ACTION_NAME="status"
ENV_NAME="production"
SHOW_HELP='0'
NODE_NAME=''
# 参数处理
for((INDEX=1; INDEX<=$#; INDEX++));do
    case "${@:$INDEX:1}" in
        -h|--help)
            SHOW_HELP='1'
        ;;
        --node)
            NODE_NAME=${@:((++INDEX)):1}
        ;;
        *)
            ITEM=${@:$INDEX:1}
            if [ -z "$ACTION_NAME" ];then
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
        ;;
    esac
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
                    status      查看服务状态，默认
                    connections 查看服务连接信息
    env         服务运行环境配置，主要有：
                    local       本地环境
                    test        测试环境
                    preview     预发环境
                    production  生产环境，默认
Options:
    --node=name 指定节点名，分布式启动时必选
    -h, --help  查看帮助脚本信息
"
    exit
fi

# 获取根目录
SERVER_BASH_PATH=$(cd "$(dirname "${BASH_SOURCE[0]}")/"; pwd)

# 参数验证
if [ -z "$ACTION_NAME" ];then
    echo "请指定操作名"
    exit 1
fi
if [ -z "$ENV_NAME" ];then
    echo "请指定环境名"
    exit 1
fi
if [ -e $PHP_PATH ] || which $PHP_PATH;then
    echo "请安装并配置PHP"
    exit 1
fi
# 执行操作
if [[ $ACTION_NAME =~ ^(start|restart)$ ]];then
    nohup $PHP_PATH $SERVER_BASH_PATH/start.php $ACTION_NAME --env=$ENV_NAME --node=$NODE_NAME 2>&1 >$SERVER_BASH_PATH/logs/$(date '+%Y-%m-%d-%H-%M-%S').log &
    bash $0 status $ENV_NAME
else
    $PHP_PATH $SERVER_BASH_PATH/start.php $ACTION_NAME --env=$ENV_NAME --node=$NODE_NAME
fi
