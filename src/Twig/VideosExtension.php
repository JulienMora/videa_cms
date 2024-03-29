<?php

namespace App\Twig;

use App\Utils\YoutubeHelper;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\EntityManager;
use App\Entity\User;
use App\Entity\UploadedVideo;
use App\Entity\ProviderVideo;

class VideosExtension extends \Twig_Extension
{
	/**
     * @var EntityManager
     */
    protected $em;

    protected $youtubeHelper;

    /**
     * GetProvinceExtension constructor.
     * @param EntityManager $em
     * @param YoutubeHelper $youtubeHelper
     */
    public function __construct(EntityManager $em, YoutubeHelper $youtubeHelper) {
        $this->em = $em;
        $this->youtubeHelper = $youtubeHelper;
    }


	public function getFunctions()
	{
		return array(
			new \Twig_SimpleFunction('getUpVideoUrl', array($this, 'getUpVideoUrl')),
			new \Twig_SimpleFunction('getExtVideoUrl', array($this, 'getExtVideoUrl')),
			new \Twig_SimpleFunction('getLastVideos', array($this, 'getLastVideos')),
			new \Twig_SimpleFunction('getLastVideoForUser', array($this, 'getLastVideoForUser')),
			new \Twig_SimpleFunction('getUrlId', array($this, 'getUrlId')),
            new \Twig_SimpleFunction('getLikeCountForId', array($this, 'getLikeCountForId')),
            new \Twig_SimpleFunction('getCommentCountForId', array($this, 'getCommentCountForId')),
		);
	}

	public function getUpVideoUrl($id){
		if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off'){
			$proto = "https://";
		}
		else{
			$proto = "http://";
		}
		return $proto.$_SERVER['SERVER_NAME']."/videos/view/u".$id;
	}

	public function getExtVideoUrl($id){
		if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off'){
			$proto = "https://";
		}
		else{
			$proto = "http://";
		}
		return $proto.$_SERVER['SERVER_NAME']."/videos/view/v".$id;
	}

	public function getLastVideos($limit = 5){
        $providerVideos = $this->em->getRepository(ProviderVideo::class)->findAll();
        $uploadedVideos = $this->em->getRepository(UploadedVideo::class)->findAll();
        $videos = array_merge($providerVideos, $uploadedVideos);
        usort($videos, function($a, $b) {
            if ($a == $b) {
                return 0;
            }
            return $a < $b ? 1 : -1;
        });
		return $videos;
	}

	public function getLastVideoForUser(User $user){
        $providerVideo = $this->em->getRepository(ProviderVideo::class)->findOneBy(["user" => $user], ['id' => 'DESC']);
        $uploadedVideo = $this->em->getRepository(UploadedVideo::class)->findOneBy(["user" => $user], ['id' => 'DESC']);

        if($providerVideo->getCreatedAt() > $uploadedVideo->getCreatedAt()){
            return $providerVideo;
        }
        else{
            return $uploadedVideo;
        }
    }


    public function getUrlId(ProviderVideo $video)
    {
        return $video->getUrlId();
    }

    public function getLikeCountForId($id)
    {
        $likeCount = $this->youtubeHelper->getVideoInfo($id);
        if (is_object($this->youtubeHelper->getVideoInfo($id)) != null) {
            return $likeCount->likeCount;
        }
    }


    public function getCommentCountForId($id){
        $commentCount = $this->youtubeHelper->getVideoInfo($id);
        if(is_object($this->youtubeHelper->getVideoInfo($id)) != null){
            return $commentCount->commentCount;
        } else {
            return $commentCount;
        }

    }
}