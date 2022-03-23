<?php

namespace Shelton\Firebase\Google\Firestore;

interface Firestorable
{
    public function getFirestoreId() : string;
    public function toFirestore(bool $new = false) : array;
}
