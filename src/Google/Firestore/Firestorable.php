<?php

namespace MurrEquip\Firebase\Google\Firestore;

interface Firestorable
{
    public function getFirestoreId() : string;
    public function toFirestore(bool $new = false) : array;
}
