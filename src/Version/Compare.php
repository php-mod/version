<?php
namespace Version;

class Compare
{
    public function compare($v1, $v2)
    {
        $va1 = ($v1 instanceof Version) ? $v1 : Version::parse($v1);
        $va2 = ($v2 instanceof Version) ? $v2 : Version::parse($v2);

        return($va1->compare($va2));
    }
}
