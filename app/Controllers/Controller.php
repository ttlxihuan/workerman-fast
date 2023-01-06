<?php

/*
 * 控制器基类，所有控制器类应该继承此类
 */

namespace App\Controllers;

/**
 * @register(name="request", key="type", method="")
 * @register(name="useWmiddleware", attach="request")
 * @register(name="validator", key="name", attach="request")
 */
abstract class Controller {
    
}
