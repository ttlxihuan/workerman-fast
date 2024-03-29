#!/bin/bash
# bash启动服务脚本，脚本允许做一些基础服务管理
# 脚本文件位置不可移动，允许非脚本目录下运行

PHP_PATH=php
ACTION_NAME=""
ENV_NAME="production"
USER_GROUP="workerman"
SHOW_HELP='0'
NODE_NAME=''
# 参数处理
for((INDEX=1; INDEX<=$#; INDEX++));do
    case "${@:$INDEX:1}" in
        -h|--help)
            SHOW_HELP='1'
        ;;
        --node=*)
            NODE_NAME=${@:$INDEX:1}
            NODE_NAME=${NODE_NAME#*=}
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
if [ $SHOW_HELP = '1' ];then
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
SERVER_BASH_PATH=$(cd "$(dirname "${BASH_SOURCE[0]}")"; pwd)

# 参数验证
if [ -z "$ACTION_NAME" ];then
    ACTION_NAME="status"
fi
if [ -z "$ENV_NAME" ];then
    echo "请指定环境名"
    exit 1
fi
# 创建用户组
if ! id "$USER_GROUP" >/dev/null 2>/dev/null && ! useradd -M -U -s '/sbin/nologin' $USER_GROUP;then
    echo "创建启动用户失败"
    exit 1
fi
if [ "$USER_GROUP" = 'root' ];then
    echo "不建议使用root启动服务"
fi
if [ -e $PHP_PATH ] || which $PHP_PATH >/dev/null 2>/dev/null;then
    # 执行操作
    if [[ $ACTION_NAME =~ ^(start|restart|status)$ ]];then
        sudo -u $USER_GROUP $PHP_PATH $SERVER_BASH_PATH/server.php $ACTION_NAME --env=$ENV_NAME --node=$NODE_NAME -d
    else
        sudo -u $USER_GROUP $PHP_PATH $SERVER_BASH_PATH/server.php $ACTION_NAME --env=$ENV_NAME --node=$NODE_NAME
    fi
else
    echo "请安装并配置PHP"
    exit 1
fi
