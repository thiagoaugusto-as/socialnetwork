<?php

namespace src\handlers;

use src\models\Post;
use src\models\User;
use src\models\UserRelation;

class PostHandler
{
    public static function createPost($id_user, $type, $body)
    {
        $body = trim($body);
        if (!empty($id_user) && !empty($body)) {
            Post::insert([
                'id_user' => $id_user,
                'type' => $type,
                'body' => $body,
                'created_at' => date('Y-m-d H:i:s')
            ])->execute();
        }
    }

    public static function _postListObject($postList, $loggedUserId) {

      // transformando o resultado em objetos dos models
      $posts = [];
      foreach ($postList as $postItem) {
        $newPost = new Post();
        $newPost->id = $postItem['id'];
        $newPost->type = $postItem['type'];
        $newPost->created_at = $postItem['created_at'];
        $newPost->body = $postItem['body'];
        $newPost->mine = false;

        if($postItem['id_user'] == $loggedUserId) {
          $newPost->mine = true;
        }

        // Preenchendo as informações adicionais no posts
        $newUser = User::select()->where('id', $postItem['id_user'])->one();
        $newPost->user = new User();
        $newPost->user->id = $newUser['id'];
        $newPost->user->name = $newUser['name'];
        $newPost->user->avatar = $newUser['avatar'];

        // preencher informacoes de LIKE
        $newPost->likeCount = 0;
        $newPost->liked = false;

        // Preencher informacoes de COMMENTS
        $newPost->comments = [];

        $posts[] = $newPost;

      }

      return $posts;

    }

    public static function getUserFeed($idUser, $page, $loggedUserId) {

      $perPage = 2;

      $postList = Post::select()
          ->where('id_user', $idUser)
          ->orderby('created_at', 'desc')
          ->page($page, $perPage)
      ->get();

      $total = Post::select()
          ->where('id_user', $idUser)
      ->count();
      $pageCount = ceil($total/$perPage);

      $posts = self::_postListObject($postList, $idUser);

      // Retornanando o resultado
      return [
        'posts' => $posts,
        'pageCount' => $pageCount,
        'currentPage' => $page
      ];

    }

    public static function getHomeFeed($id_user, $page)
    {

        $perPage = 2;

        // Pegando a lista de usuarios que eu sigo
        $user_list = UserRelation::select()->where('user_from', $id_user)->get();
        $users = [];
        foreach ($user_list as $user_item) {
            $users[] = $user_item['user_to'];
        }
        $users[] = $id_user;

        // Pegando os posts de todos que eu sigo ordenado pela SDO_DAS_DataObject
        $postList = Post::select()
            ->where('id_user', 'in', $users)
            ->orderby('created_at', 'desc')
            ->page($page, $perPage)
        ->get();

        $total = Post::select()
            ->where('id_user', 'in', $users)
        ->count();
        $pageCount = ceil($total/$perPage);

        $posts = self::_postListObject($postList, $id_user);

        // Retornanando o resultado
        return [
          'posts' => $posts,
          'pageCount' => $pageCount,
          'currentPage' => $page
        ];
    }

    public static function getPhotosFrom($id_user) {
      $photosData = Post::select()
        ->where('id_user', $id_user)
        ->where('type', 'photo')
      ->get();


      $photos = [];

      foreach ($photosData as $photo) {
        $newPost = new Post();
        $newPost->id = $photo['id'];
        $newPost->type = $photo['type'];
        $newPost->created_at = $photo['created_at'];
        $newPost->body = $photo['body'];

        $photos[] = $newPost;
      }

      return $photos;
    }

}
