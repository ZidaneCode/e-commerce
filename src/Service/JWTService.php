<?php
namespace App\Service;

use DateTimeImmutable;

class JWTService
{
    // on cree un token
    public function generate(array $header,array $payload,string $secret,int $validity=10800):string
    {
        if ($validity>0) {
            $now=new DateTimeImmutable();
            $exp=$now->getTimestamp()+$validity;
            $payload['iat']=$now->getTimestamp();
            $payload['exp']=$exp;
        }


        //on encode en base64
        $base64Header=base64_encode(json_encode($header));
        $base64Payload=base64_encode(json_encode($payload));
        //on nettoie les valeurs encodé comme / ,+ ,=

        $base64Header=str_replace(['+','/','='],['-','_',''],$base64Header);
        $base64Payload=str_replace(['+','/','='],['-','_',''],$base64Payload);
        //on gener la signature
        $secret=base64_encode($secret);

        $signature=hash_hmac('sha256',$base64Header.'.'.$base64Payload,$secret,true);
        
        $base64signature=base64_encode($signature);

        $base64signature=str_replace(['+','/','='],['-','_',''],$base64signature);

        $jwt=$base64Header.'.'.$base64Payload.'.'.$base64signature;



        return $jwt;

    }
    //on vérife si le token est valide (correctement formé)
    public function isValide(string $token): bool
    {
        return preg_match('/^[a-zA-Z0-9\-\_\=]+\.[a-zA-Z0-9\-\_\=]+\.[a-zA-Z0-9\-\_\=]+$/',$token)===1;
    }
    //on recuper le payload pour vérifie 
    public function getPayload(string $token): array
    {
        //on démonte le token 
        $array=explode('.',$token);
        //on decode le payload
        $payload=json_decode(base64_decode($array[1]),true);
        return $payload;
    }

     //on recuper le header pour vérifie 
     public function getHeader(string $token): array
     {
         //on démonte le token 
         $array=explode('.',$token);
         //on decode le header
         $header=json_decode(base64_decode($array[0]),true);
         return $header;
     }

    //on vérif si le token n'est pas expiré
    public function isExpired($token):bool
    {
        $payload= $this->getPayload($token);
        $now=new DateTimeImmutable();
        return( $payload['exp']<$now->getTimestamp());
    }
    //on vérifie la signature de token
    public function check(String $token,String $secret): bool
    {
        //on récuper le header et le payload
        $payload= $this->getPayload($token);
        $header= $this->getHeader($token);
         //on regénére le token
         $verifToken=$this->generate($header,$payload,$secret,0);
         
         return $verifToken===$token;
    }
}