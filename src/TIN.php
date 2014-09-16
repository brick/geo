<?php

namespace Brick\Geo;

/**
 * @todo this class is not supported yet, but stays here for now as a draft.
 */
class TIN extends PolyhedralSurface
{
    /**
     * Builds a TIN from an array of Triangle objects
     *
     * @param Triangle[] $patches
     *
     * @return TIN
     *
     * @throws GeometryException
     */
    public static function factory(array $patches)
    {
        foreach ($patches as $patch) {
            if (! $patch instanceof Triangle) {
                throw new GeometryException('A TIN can only contain Triangle objects');
            }
        }

        return new TIN($patches);
    }

    /**
     * {@inheritdoc}
     */
    public function geometryType()
    {
        return 'TIN';
    }
}
