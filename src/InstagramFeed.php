<?php


namespace Instaty;


class InstagramFeed
{
  
  private $username;

  private $numberPosts;

  private $content;

  private $postId;

  private $postImg;

  private $postText;
  
  private $postTime;


  public function __construct(string $username,int $numberPosts){
    
    $this->username = $username;

    $this->numberPosts = $numberPosts;

    $this->content = $this->getContent();

    $this->postText = $this->getPostData("/\"text\":\"(.*)\"}}]},\"shortcode/U");

    $this->postImg = $this->getPostData("/display_url\":\"(.*)\",\"edge_/U");

    $this->postId = $this->getPostData("/shortcode\":\"(.*)\",\"edge_media/U");

    $this->postTime = $this->getPostData("/taken_at_timestamp\":(.*),\"dim/U");
    
  }

  public function getFeeds(){

      return $this->fullPost();
  }
  
  private function getPostData($pattern){
    
    preg_match_all($pattern, $this->content, $data);

    $data = array_slice($data[1],0,$this->numberPosts);
    
    return $data;
  }

  private function fullPost(){
    
    $username = $this->username;

    $full = array_map(function($t,$i,$time,$ids) use ($username) {

        $post =(object)[];

        $post->id = $ids;

        $post->page = $username;

        $post->timestamp = $time;

        $post->time = date('d-m-Y H:i', $time);

        $post->txt = html_entity_decode($t);

        $post->linkPost = $this->getLinkPost($ids);

        $post->img = $i;

        return $post;

    },$this->postText,$this->postImg,$this->postTime,$this->postId);

    return $full;
  }

  private function getLinkPost($id){

    return "https://www.instagram.com/p/".$id."/";
  }

  private function getContent(){

    $url = "https://www.instagram.com/".$this->username."/";

    $opts = ['http' => [ 'header' => "User-Agent:MyAgent/1.0\r\n"]]; 

    $context = stream_context_create($opts);

    $content = file_get_contents($url,false,$context);

    return $content;
  }
}