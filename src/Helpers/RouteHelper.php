<?php

namespace App\Helpers;

class RouteHelper
{
    public static function ADMIN_ROUTE($paths)
    {   
        $datas = [];
        foreach ($paths as $path){
            if(
                preg_match('#^/api/private/admin#', $path->getChemin()) or
                preg_match('#^/printer/admin#', $path->getChemin())
            ) {
                $datas[] = $path;
            }
        }
        return $datas;
    }

    public static function CONSTRUCTION_ROUTE($paths)
    {
        $datas = [];
        foreach ($paths as $path){
            if(
                preg_match('#^/api/private/agency/construction#', $path->getChemin()) or 
                preg_match('#^/api/private/agency/funding#', $path->getChemin()) or 
                preg_match('#^/api/private/agency/funding/payment#', $path->getChemin()) or 
                preg_match('#^/api/private/agency/production#', $path->getChemin()) or 
                preg_match('#^/api/private/agency/quote#', $path->getChemin()) or 
                preg_match('#^/api/private/agency/house#', $path->getChemin())
            ) {
                $datas[] = $path;
            }
        }
        return $paths;
    }

    // La fonction avec RESTRICTED permet de limiter les action: Empêcher la suppression (delete)....
    public static function CONSTRUCTION_RESTRICTED_ROUTE($paths)
    {   
        $datas = RouteHelper::CONSTRUCTION_ROUTE($paths);
        foreach ($paths as $path){
            if(
                preg_match('#delete#', $path->getNom()) or 
                preg_match('#validate#', $path->getNom()) or 
                preg_match('#activate#', $path->getNom())
            ) {
                unset($datas[$path]);
            }
        }
        return $datas;
    }

    public static function CUSTOMER_ROUTE($paths)
    {
        $datas = [];
        foreach ($paths as $path){
            if(
                preg_match('#^/api/private/agency/customer#', $path->getChemin()) or 
                preg_match('#^/api/private/agency/folder/customer#', $path->getChemin()) or 
                preg_match('#^/api/private/agency/folder/terminate#', $path->getChemin()) or 
                preg_match('#^/api/private/agency/mutate#', $path->getChemin()) or 
                preg_match('#^/api/private/agency/payment/customer#', $path->getChemin()) or 
                preg_match('#^/api/private/agency/user#', $path->getChemin())
            ) {
                $datas[] = $path;
            }
        }
        return $datas;
    }

    public static function CUSTOMER_RESTRICTED_ROUTE($paths)
    {   
        $datas = RouteHelper::CUSTOMER_ROUTE($paths);
        $row  = [];
        foreach ($paths as $path){
            if(
                preg_match('#delete#', $path->getNom()) or 
                preg_match('#validate#', $path->getNom()) or 
                preg_match('#activate#', $path->getNom())
            ) {}else{
                $row[] = $path;
            }
        }
        return $datas;
    }

    public static function TENANT_ROUTE($paths)
    {   
        $datas = [];
        foreach ($paths as $path){
            if(
                preg_match('#^/api/private/agency/tenant#', $path->getChemin()) or 
                preg_match('#^/api/private/agency/owner#', $path->getChemin()) or 
                preg_match('#^/api/private/agency/contract#', $path->getChemin()) or 
                preg_match('#^/api/private/agency/invoice#', $path->getChemin()) or 
                preg_match('#^/api/private/agency/inventory#', $path->getChemin()) or 
                preg_match('#^/api/private/agency/notice#', $path->getChemin()) or 
                preg_match('#^/api/private/agency/payment#', $path->getChemin()) or 
                preg_match('#^/api/private/agency/penality#', $path->getChemin()) or 
                preg_match('#^/api/private/agency/renew/contract#', $path->getChemin()) or 
                preg_match('#^/api/private/agency/rent#', $path->getChemin()) or 
                preg_match('#^/api/private/agency/house#', $path->getChemin()) or 
                preg_match('#^/api/private/agency/rental#', $path->getChemin()) or 
                preg_match('#^/api/private/agency/terminate#', $path->getChemin()) or 
                preg_match('#^/api/private/agency/mandate#', $path->getChemin()) or 
                preg_match('#^/api/private/agency/renew/mandate#', $path->getChemin())
            ) {
                $datas[] = $path;
            }
        }
        return $datas;
    }

    public static function TENANT_RESTRICTED_ROUTE($paths)
    {   
        $datas = RouteHelper::TENANT_ROUTE($paths);
        $row  = [];
        foreach ($datas as $path){
            if(
                preg_match('#delete#', $path->getNom()) or 
                preg_match('#validate#', $path->getNom()) or 
                preg_match('#activate#', $path->getNom())
            ) {}else{
                $row[] = $path;
            }
        }
        return $row;
    }

    public static function PATRIMOINE_ROUTE($paths)
    {   
        $datas = [];
        foreach ($paths as $path){
            if(
                preg_match('#^/api/private/agency/home#', $path->getChemin()) or 
                preg_match('#^/api/private/agency/home/type#', $path->getChemin()) or 
                preg_match('#^/api/private/agency/promotion#', $path->getChemin()) or 
                preg_match('#^/api/private/agency/mutate#', $path->getChemin()) or 
                preg_match('#^/api/private/agency/payment/customer#', $path->getChemin()) or 
                preg_match('#^/api/private/agency/subdivision#', $path->getChemin()) or 
                preg_match('#^/api/private/agency/islet#', $path->getChemin()) or 
                preg_match('#^/api/private/agency/lot#', $path->getChemin()) or 
                preg_match('#^/api/private/agency/folder#', $path->getChemin())
            ) {
                $datas[] = $path;
            }
        }
        return $datas;
    }

    public static function PATRIMOINE_RESTRICTED_ROUTE($paths)
    {
        $datas = RouteHelper::PATRIMOINE_ROUTE($paths);
        $row  = [];
        foreach ($paths as $path){
            if(
                preg_match('#delete#', $path->getNom()) or 
                preg_match('#validate#', $path->getNom()) or 
                preg_match('#activate#', $path->getNom())
            ) {}else{
                $row[] = $path;
            } 
        }
        return $datas;
    }

    public static function OWNER_ROUTE($paths)
    {   
        $datas = [];
        foreach ($paths as $path){
            if(
                preg_match('#^/api/private/agency/tenant#', $path->getChemin()) or 
                preg_match('#^/api/private/agency/owner#', $path->getChemin()) or  
                preg_match('#^/api/private/agency/house#', $path->getChemin()) or 
                preg_match('#^/api/private/agency/rental#', $path->getChemin()) or
                preg_match('#^/api/private/agency/renew/mandate#', $path->getChemin()) or 
                preg_match('#^api/private/agency/repayment#', $path->getChemin()) or 
                preg_match('#^/api/private/agency/rent#', $path->getChemin())
            ) {
                $datas[] = $path;
            }
        }
        return $datas;
    }

    public static function OWNER_RESTRICTED_ROUTE($paths)
    {   
        $datas = RouteHelper::OWNER_ROUTE($paths);
        $row  = [];
        foreach ($paths as $path){
            if(
                preg_match('#delete#', $path->getNom()) or 
                preg_match('#validate#', $path->getNom()) or 
                preg_match('#activate#', $path->getNom())
            ) {}else{
                $row[] = $path;
            }
        }
        return $datas;
    }

    public static function TREASURY_ROUTE($paths)
    {   
        $datas = [];
        foreach ($paths as $path){
            if(
                preg_match('#^/api/private/agency/fund/request#', $path->getChemin()) or 
                preg_match('#^/api/private/agency/treasury#', $path->getChemin()) or 
                preg_match('#^/api/private/agency/day#', $path->getChemin()) or 
                preg_match('#^/api/private/agency/spent#', $path->getChemin()) or 
                preg_match('#^/api/private/agency/repayment/payment#', $path->getChemin()) or 
                preg_match('#^/api/private/agency/supply#', $path->getChemin())
            ) {
                $datas[] = $path;
            }
        }
        return $datas;
    }

    public static function TREASURY_RESTRICTED_ROUTE($paths)
    {   
        $datas = RouteHelper::TREASURY_ROUTE($paths);
        $row  = [];
        foreach ($paths as $path){
            if(
                preg_match('#delete#', $path->getNom()) or 
                preg_match('#validate#', $path->getNom()) or 
                preg_match('#activate#', $path->getNom()) or 
                preg_match('#end#', $path->getNom()) or 
                preg_match('#send#', $path->getNom())
            ) {}else{
                $row[] = $path;
            }
        }
        return $datas;
    }
}
