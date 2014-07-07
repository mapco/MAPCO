<?php
	/**************************************************
	***	Class: MGenerate							***
	***	Namespace: Mapco.Helper.Generate			***
	*** Author: C.Haendler <chaendler(at)mapco.de> 	***
	***	Version: 1.0  		01/07/14/ 				***
	***	Last mod: 01/07/14							***
	***************************************************/

	class MGenerate {

		/*
		*	Generate a unique user id
		*
		*/

		public static function UUID()
		{
			$UUID = sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
			mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),
			mt_rand( 0, 0x0fff ) | 0x4000,
			mt_rand( 0, 0x3fff ) | 0x8000,
			mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ) );
			return $UUID;
		}

		/*
		*	Generate a new password
		*
		*/

		public static function Password ($lenght)
		{
			if (empty($lenght))
			{
				$cod_l = 8;
			}
			else
			{
				$cod_1 = $lenght;
			}
			$zeichen = "a,b,c,d,e,f,g,h,i,j,k,l,m,n,o,p,q,r,s,t,u,v,w,x,y,z,0,1,2,3,4,5,6,7,8,9,A,B,C,D,E,F,G,H,I,J,K,L,M,N,O,P,Q,R,S,T,U,V,W,X,Y,Z,@,!,?,_,-,#,+,=";
			$array_b = explode(",",$zeichen);
			$key = "";
			for($i=0;$i<$cod_l;$i++)
			{
				srand((double)microtime()*1000000);
				$z = rand(0,35);
				$key .= "".$array_b[$z]."";
			}
			return $key;
		}

		/*
		*	Generate a new key
		*
		*/

		public static function Key($lenght)
		{
			if (empty($lenght))
			{
				$cod_l = 10;
			}
			else
			{
				$cod_1 = $lenght;
			}
			$zeichen = "a,b,c,d,e,f,g,h,i,j,k,l,m,n,o,p,q,r,s,t,u,v,w,x,y,z,0,1,2,3,4,5,6,7,8,9";
			$array_b = explode(",",$zeichen);
			$key = "";
			for($i=0;$i<$cod_l;$i++)
			{
				srand((double)microtime()*1000000);
				$z = rand(0,35);
				$key .= "".$array_b[$z]."";
			}
			return $key;
		}

		/*
		*	Generate a new token
		*
		*/

		public static function Token($length = 32)
		{
			static $chars = '0123456789abcdef';
			$max = strlen($chars) - 1;
			$token = '';
			$name = session_name();
			for ($i = 0; $i < $length; ++$i)
			{
				$token .= $chars[(rand(0, $max))];
			}
			return md5($token . $name);
		}

	}
