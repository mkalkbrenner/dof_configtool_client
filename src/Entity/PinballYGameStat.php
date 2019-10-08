<?php

namespace App\Entity;

class PinballYGameStat
{
    use TrackChangesTrait;

    /**
     * @var string
     */
    private $game;

    /**
     * @var string
     */
    private $lastPlayed;

    /**
     * @var string
     */
    private $playCount;

    /**
     * @var string
     */
    private $playTime;

    /**
     * @var string
     */
    private $isFavorite;

    /**
     * @var string
     */
    private $rating;

    /**
     * @var string
     */
    private $audioVolume;

    /**
     * @var string
     */
    private $categories;

    /**
     * @var string
     */
    private $isHidden;

    /**
     * @var string
     */
    private $dateAdded;

    /**
     * @var string
     */
    private $highScoreStyle;

    /**
    /**
     * @var string
     */
    private $markedForCapture;

    /**
     * @var string
     */
    private $showWhenRunning;

    /**
     * @var array
     */
    private $showWhenRunningArray = [
        'bg' => false,
        'dmd' => false,
        'topper' => false,
        'instcard' => false,
    ];

    public function __construct(string $csv)
    {
        list(
            $this->game,
            $this->lastPlayed,
            $this->playCount,
            $this->playTime,
            $this->isFavorite,
            $this->rating,
            $this->audioVolume,
            $this->categories,
            $this->isHidden,
            $this->dateAdded,
            $this->highScoreStyle,
            $this->markedForCapture,
            $this->showWhenRunning
            ) = array_pad(str_getcsv($csv), 13,'');

        foreach ($this->showWhenRunningArray as $screen => &$value) {
            $value = strpos($this->showWhenRunning, $screen) !== false;
        }
    }

    /**
     * @return string
     */
    public function getGame(): string
    {
        return $this->game;
    }

    /**
     * @param string $game
     * @return PinballYGameStat
     */
    public function setGame(string $game): PinballYGameStat
    {
        if ($this->trackChanges && $this->game != $game) {
            $this->hasChanges['game'] = TRUE;
        }
        $this->game = $game;
        return $this;
    }

    /**
     * @return string
     */
    public function getGameTable(): string
    {
        if (preg_match('/(.+)\.[^\.]+$/', $this->game, $matches)) {
            return $matches[1];
        }
        return '';
    }

    /**
     * @return string
     */
    public function getGameGroup(): string
    {
        if (preg_match('/.+\.([^\.]+)$/', $this->game, $matches)) {
            return $matches[1];
        }
        return '';
    }

    /**
     * @return string
     */
    public function getLastPlayed(): string
    {
        return $this->lastPlayed;
    }

    /**
     * @param string $lastPlayed
     * @return PinballYGameStat
     */
    public function setLastPlayed(string $lastPlayed): PinballYGameStat
    {
        if ($this->trackChanges && $this->lastPlayed != $lastPlayed) {
            $this->hasChanges['lastPlayed'] = TRUE;
        }
        $this->lastPlayed = $lastPlayed;
        return $this;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getLastPlayedDateTime(): ?\DateTimeInterface
    {
        return \DateTimeImmutable::createFromFormat('YmdHis', $this->lastPlayed) ?: null;
    }

    /**
     * @return string
     */
    public function getPlayCount(): string
    {
        return $this->playCount;
    }

    /**
     * @param string $playCount
     * @return PinballYGameStat
     */
    public function setPlayCount(string $playCount): PinballYGameStat
    {
        if ($this->trackChanges && $this->playCount != $playCount) {
            $this->hasChanges['playCount'] = TRUE;
        }
        $this->playCount = $playCount;
        return $this;
    }

    /**
     * @return string
     */
    public function getPlayTime(): string
    {
        return $this->playTime;
    }

    /**
     * @param string $playTime
     * @return PinballYGameStat
     */
    public function setPlayTime(string $playTime): PinballYGameStat
    {
        if ($this->trackChanges && $this->playTime != $playTime) {
            $this->hasChanges['playTime'] = TRUE;
        }
        $this->playTime = $playTime;
        return $this;
    }

    /**
     * @return string
     */
    public function getIsFavorite(): string
    {
        return $this->isFavorite;
    }

    /**
     * @param string $isFavorite
     * @return PinballYGameStat
     */
    public function setIsFavorite(string $isFavorite): PinballYGameStat
    {
        if ($this->trackChanges && $this->isFavorite != $isFavorite) {
            $this->hasChanges['isFavorite'] = TRUE;
        }
        $this->isFavorite = $isFavorite;
        return $this;
    }

    /**
     * @return string
     */
    public function getRating(): string
    {
        return $this->rating;
    }

    /**
     * @param string $rating
     * @return PinballYGameStat
     */
    public function setRating(string $rating): PinballYGameStat
    {
        if ($this->trackChanges && $this->rating != $rating) {
            $this->hasChanges['rating'] = TRUE;
        }
        $this->rating = $rating;
        return $this;
    }

    /**
     * @return string
     */
    public function getAudioVolume(): string
    {
        return $this->audioVolume;
    }

    /**
     * @param string $audioVolume
     * @return PinballYGameStat
     */
    public function setAudioVolume(string $audioVolume): PinballYGameStat
    {
        if ($this->trackChanges && $this->audioVolume != $audioVolume) {
            $this->hasChanges['audioVolume'] = TRUE;
        }
        $this->audioVolume = $audioVolume;
        return $this;
    }

    /**
     * @return string
     */
    public function getCategories(): string
    {
        return $this->categories;
    }

    /**
     * @param string $categories
     * @return PinballYGameStat
     */
    public function setCategories(string $categories): PinballYGameStat
    {
        if ($this->trackChanges && $this->categories != $categories) {
            $this->hasChanges['categories'] = TRUE;
        }
        $this->categories = $categories;
        return $this;
    }

    /**
     * @return string
     */
    public function getIsHidden(): string
    {
        return $this->isHidden;
    }

    /**
     * @param string $isHidden
     * @return PinballYGameStat
     */
    public function setIsHidden(string $isHidden): PinballYGameStat
    {
        if ($this->trackChanges && $this->isHidden != $isHidden) {
            $this->hasChanges['isHidden'] = TRUE;
        }
        $this->isHidden = $isHidden;
        return $this;
    }

    /**
     * @return string
     */
    public function getDateAdded(): string
    {
        return $this->dateAdded;
    }

    /**
     * @param string $dateAdded
     * @return PinballYGameStat
     */
    public function setDateAdded(string $dateAdded): PinballYGameStat
    {
        if ($this->trackChanges && $this->dateAdded != $dateAdded) {
            $this->hasChanges['dateAdded'] = TRUE;
        }
        $this->dateAdded = $dateAdded;
        return $this;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getDateAddedDateTime(): ?\DateTimeInterface
    {
        return \DateTimeImmutable::createFromFormat('YmdHis', $this->dateAdded) ?: null;
    }

    /**
     * @return string
     */
    public function getHighScoreStyle(): string
    {
        return $this->highScoreStyle;
    }

    /**
     * @param string $highScoreStyle
     * @return PinballYGameStat
     */
    public function setHighScore(string $highScoreStyle): PinballYGameStat
    {
        if ($this->trackChanges && $this->highScoreStyle != $highScoreStyle) {
            $this->hasChanges['highScoreStyle'] = TRUE;
        }
        $this->highScoreStyle = $highScoreStyle;
        return $this;
    }

    /**
     * @return string
     */
    public function getMarkedForCapture(): string
    {
        return $this->markedForCapture;
    }

    /**
     * @param string $markedForCapture
     * @return PinballYGameStat
     */
    public function setMarkedForCapture(string $markedForCapture): PinballYGameStat
    {
        if ($this->trackChanges && $this->markedForCapture != $markedForCapture) {
            $this->hasChanges['markedForCapture'] = TRUE;
        }
        $this->markedForCapture = $markedForCapture;
        return $this;
    }

    /**
     * @return string
     */
    public function getShowWhenRunning(): string
    {
        return $this->showWhenRunning;
    }

    /**
     * @param string $showWhenRunning
     * @return PinballYGameStat
     */
    public function setShowWhenRunning(string $showWhenRunning): PinballYGameStat
    {
        if ($this->trackChanges && $this->showWhenRunning != $showWhenRunning) {
            $this->hasChanges['showWhenRunning'] = TRUE;
        }
        $this->showWhenRunning = $showWhenRunning;
        return $this;
    }

    /**
     * @return bool
     */
    public function isDmdShownWhenRunning(): bool
    {
        return $this->showWhenRunningArray['dmd'];
    }

    /**
     * @param bool $show
     * @return PinballYGameStat
     */
    public function setDmdShownWhenRunning(bool $show): PinballYGameStat
    {
        $this->showWhenRunningArray['dmd'] = $show;
        return $this->setShowWhenRunning(implode(' ', array_keys(array_filter($this->showWhenRunningArray))));
    }

    /**
     * @return bool
     */
    public function isBackglassShownWhenRunning(): bool
    {
        return $this->showWhenRunningArray['bg'];
    }

    /**
     * @param bool $show
     * @return PinballYGameStat
     */
    public function setBackglassShownWhenRunning(bool $show): PinballYGameStat
    {
        $this->showWhenRunningArray['bg'] = $show;
        return $this->setShowWhenRunning(implode(' ', array_keys(array_filter($this->showWhenRunningArray))));
    }

    /**
     * @return bool
     */
    public function isTopperShownWhenRunning(): bool
    {
        return $this->showWhenRunningArray['topper'];
    }

    /**
     * @param bool $show
     * @return PinballYGameStat
     */
    public function setTopperShownWhenRunning(bool $show): PinballYGameStat
    {
        $this->showWhenRunningArray['topper'] = $show;
        return $this->setShowWhenRunning(implode(' ', array_keys(array_filter($this->showWhenRunningArray))));
    }

    /**
     * @return bool
     */
    public function isInstructionCardShownWhenRunning(): bool
    {
        return $this->showWhenRunningArray['instcard'];
    }

    /**
     * @param bool $show
     * @return PinballYGameStat
     */
    public function setInstructionCardShownWhenRunning(bool $show): PinballYGameStat
    {
        $this->showWhenRunningArray['instcard'] = $show;
        return $this->setShowWhenRunning(implode(' ', array_keys(array_filter($this->showWhenRunningArray))));
    }

    public function toArray() {
        return [
            'game' => $this->game,
            'lastPlayed' => $this->lastPlayed,
            'playCount' => $this->playCount,
            'playTime' => $this->playTime,
            'isFavorite' => $this->isFavorite,
            'rating' => $this->rating,
            'audioVolume' => $this->audioVolume,
            'categories' => $this->categories,
            'isHidden' => $this->isHidden,
            'dateAdded' => $this->dateAdded,
            'highScoreStyle' => $this->highScoreStyle,
            'markedForCapture' => $this->markedForCapture,
            'showWhenRunning' => $this->showWhenRunning,
        ];
    }

    public function getCsv() {
        return
            $this->game . ',' .
            $this->lastPlayed . ',' .
            $this->playCount . ',' .
            $this->playTime . ',' .
            $this->isFavorite . ',' .
            $this->rating . ',' .
            $this->audioVolume . ',' .
            $this->categories . ',' .
            $this->isHidden . ',' .
            $this->dateAdded . ',' .
            $this->highScoreStyle . ',' .
            $this->markedForCapture . ',' .
            $this->showWhenRunning;
    }
}
