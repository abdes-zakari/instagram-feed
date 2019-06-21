<?php


namespace Instaty;


class InstaFeed
{
  
  private $pages;

  private $numberPosts;

  public function __construct($pages,int $numberPosts){
    
    $this->pages = $this->arrayPages($pages);

    $this->numberPosts = $numberPosts;
    
  }
  
  public function getFeeds(){
      
      $result = $this->getJson();

      $mergeed = [];

      foreach($result as $p => $res) {

          $data = json_decode($res);

          $postsInfo = $this->getPostsInfo($data);

          $username = $this->getUsername($data);

          $allPosts = $this->getPosts($postsInfo,$username);

          $mergeed = array_merge($mergeed,$allPosts);
      }

      usort($mergeed, function($a, $b) {

          return $b->timestamp - $a->timestamp;
      
      });

      return $mergeed;
  }

  private function getJson(){

      $multiCurl = [];
      
      $result = array();

      $mh = curl_multi_init();

      foreach ($this->pages as $i => $page) {

        $fetchURL = "https://www.instagram.com/".$page."/?__a=1";

        $multiCurl[$i] = curl_init();

        curl_setopt($multiCurl[$i], CURLOPT_URL,$fetchURL);

        curl_setopt($multiCurl[$i], CURLOPT_HEADER,0);

        curl_setopt($multiCurl[$i], CURLOPT_RETURNTRANSFER,1);

        curl_multi_add_handle($mh, $multiCurl[$i]);
      }

      $index=null;

      do {

        curl_multi_exec($mh,$index);

      } while($index > 0);

      foreach($multiCurl as $k => $ch) {

        $result[$k] = curl_multi_getcontent($ch);

        curl_multi_remove_handle($mh, $ch);

      }

      curl_multi_close($mh);

      return $result;
  }

  private function getPosts($postsInfo,$username){

      $allPosts = [];

      foreach($postsInfo as $j => $d) {

        if ($j < $this->numberPosts) {

            $post = (object)[];

            $post->id = $d->node->shortcode;

            $post->txt = $d->node->edge_media_to_caption->edges[0]->node->text  ?? '';

            $post->img = $d->node->display_url;

            $post->timestamp = $d->node->taken_at_timestamp;

            $post->time = date('d-m-Y H:i', $d->node->taken_at_timestamp);

            $post->page=$username;

            $allPosts[] = $post;

        }

      }

      return $allPosts;
  }

  private function arrayPages($pages){

       if (!is_array($pages))

           $pages =  explode(' ', $pages);

       return $pages; 
  }

  private function getUser($data){
      
      return $data->graphql->user;
  }

  private function getPostsInfo($data){
      
      return $this->getUser($data)->edge_owner_to_timeline_media->edges;
  }
  
  private function getUsername($data){
      
      return $this->getUser($data)->full_name;

  }
}