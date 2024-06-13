<?php

namespace App\Services\Manage\Scorm\Utils;

use App\Exceptions\Scrom\InvalidScormArchiveException;
use Illuminate\Support\Str;

class ScormLib
{
    /**
     * @throws InvalidScormArchiveException
     */
    public function parseOrganizationsNode(\DOMDocument $dom): array
    {
        $organizationsList = $dom->getElementsByTagName('organizations');
        $resources = $dom->getElementsByTagName('resource');

        if ($organizationsList->length > 0) {
            $organizations = $organizationsList->item(0);
            $organization = $organizations->firstChild;

            if (
                !is_null($organizations->attributes)
                && !is_null($organizations->attributes->getNamedItem('default'))
            ) {
                $defaultOrganization = $organizations->attributes->getNamedItem('default')->nodeValue;
            } else {
                $defaultOrganization = null;
            }
            // No default organization is defined
            if (is_null($defaultOrganization)) {
                while (
                    !is_null($organization)
                    && 'organization' !== $organization->nodeName
                ) {
                    $organization = $organization->nextSibling;
                }

                if (is_null($organization)) {
                    return $this->parseResourceNodes($resources);
                }
            }
            // A default organization is defined
            // Look for it
            else {
                while (
                    !is_null($organization)
                    && ('organization' !== $organization->nodeName
                        || is_null($organization->attributes->getNamedItem('identifier'))
                        || $organization->attributes->getNamedItem('identifier')->nodeValue !== $defaultOrganization)
                ) {
                    $organization = $organization->nextSibling;
                }

                if (is_null($organization)) {
                    throw new InvalidScormArchiveException('default_organization_not_found_message');
                }
            }

            return $this->parseItemNodes($organization, $resources);
        } else {
            throw new InvalidScormArchiveException('no_organization_found_message');
        }
    }

    /**
     * Creates defined structure of SCOs.
     *
     * @throws InvalidScormArchiveException
     *
     * @return array of Sco
     */
    private function parseItemNodes(\DOMNode $source, \DOMNodeList $resources, $parentSco = null): array
    {
        $item = $source->firstChild;
        $scos = [];

        while (!is_null($item)) {
            if ('item' === $item->nodeName) {
                $sco = collect();
                $sco->put('uuid', Str::uuid());
                $sco->put('scoParent', $parentSco ? $parentSco->toArray() : null);
                $this->findAttrParams($sco, $item, $resources);
                $this->findNodeParams($sco, $item->firstChild);

                if (data_get($sco, 'block', false)) {
                    $sco->put('scoChildren', $this->parseItemNodes($item, $resources, $sco));
                }
                $scos[] = $sco->toArray();
            }
            $item = $item->nextSibling;
        }

        return $scos;
    }

    private function parseResourceNodes(\DOMNodeList $resources): array
    {
        $scos = [];

        foreach ($resources as $resource) {
            if (!is_null($resource->attributes)) {
                $scormType = $resource->attributes->getNamedItemNS(
                    $resource->lookupNamespaceUri('adlcp'),
                    'scormType'
                );

                if (!is_null($scormType) && 'sco' === $scormType->nodeValue) {
                    $identifier = $resource->attributes->getNamedItem('identifier');
                    $href = $resource->attributes->getNamedItem('href');

                    if (is_null($identifier)) {
                        throw new InvalidScormArchiveException('sco_with_no_identifier_message');
                    }
                    if (is_null($href)) {
                        throw new InvalidScormArchiveException('sco_resource_without_href_message');
                    }
                    $sco = collect();
                    $sco->put('uuid', Str::uuid());
                    $sco->put('block', false);
                    $sco->put('visible', true);
                    $sco->put('identifier', $identifier->nodeValue);
                    $sco->put('title', $identifier->nodeValue);
                    $sco->put('entryUrl', $href->nodeValue);
                    $scos[] = $sco->toArray();
                }
            }
        }

        return $scos;
    }

    /**
     * Initializes parameters of the SCO defined in attributes of the node.
     * It also look for the associated resource if it is a SCO and not a block.
     *
     * @throws InvalidScormArchiveException
     */
    private function findAttrParams($sco, \DOMNode $item, \DOMNodeList $resources): void
    {
        $identifier = $item->attributes->getNamedItem('identifier');
        $isVisible = $item->attributes->getNamedItem('isvisible');
        $identifierRef = $item->attributes->getNamedItem('identifierref');
        $parameters = $item->attributes->getNamedItem('parameters');

        // throws an Exception if identifier is undefined
        if (is_null($identifier)) {
            throw new InvalidScormArchiveException('sco_with_no_identifier_message');
        }
        $sco->put('identifier', $identifier->nodeValue);

        // visible is true by default
        if (!is_null($isVisible) && 'false' === $isVisible) {
            $sco->put('visible', false);
        } else {
            $sco->put('visible', true);
        }

        // set parameters for SCO entry resource
        if (!is_null($parameters)) {
            $sco->put('parameters', $parameters->nodeValue);
        }

        // check if item is a block or a SCO. A block doesn't define identifierref
        if (is_null($identifierRef)) {
            $sco->put('block', true);
        } else {
            $sco->put('block', false);
            // retrieve entry URL
            $sco->put('entryUrl', $this->findEntryUrl($identifierRef->nodeValue, $resources));
        }
    }

    /**
     * Initializes parameters of the SCO defined in children nodes.
     */
    private function findNodeParams($sco, \DOMNode $item)
    {
        while (!is_null($item)) {
            switch ($item->nodeName) {
                case 'title':
                    $sco->put('title', $item->nodeValue);
                    break;
                case 'adlcp:masteryscore':
                    $sco->put('scoreToPassInt', $item->nodeValue);
                    break;
                case 'adlcp:maxtimeallowed':
                case 'imsss:attemptAbsoluteDurationLimit':
                    $sco->put('maxTimeAllowed', $item->nodeValue);
                    break;
                case 'adlcp:timelimitaction':
                case 'adlcp:timeLimitAction':
                    $action = strtolower($item->nodeValue);

                    if (
                        'exit,message' === $action
                        || 'exit,no message' === $action
                        || 'continue,message' === $action
                        || 'continue,no message' === $action
                    ) {
                        $sco->put('timeLimitAction', $action);
                    }
                    break;
                case 'adlcp:datafromlms':
                case 'adlcp:dataFromLMS':
                    $sco->put('launchData', $item->nodeValue);
                    break;
                case 'adlcp:prerequisites':
                    $sco->put('prerequisites', $item->nodeValue);
                    break;
                case 'imsss:minNormalizedMeasure':
                    $sco->put('scoreToPassDecimal', $item->nodeValue);
                    break;
                case 'adlcp:completionThreshold':
                    if ($item->nodeValue && !is_nan($item->nodeValue)) {
                        $sco->put('completionThreshold', floatval($item->nodeValue));
                    }
                    break;
            }
            $item = $item->nextSibling;
        }
    }

    /**
     * Searches for the resource with the given id and retrieve URL to its content.
     *
     * @throws InvalidScormArchiveException
     *
     * @return string URL to the resource associated to the SCO
     */
    public function findEntryUrl($identifierref, \DOMNodeList $resources)
    {
        foreach ($resources as $resource) {
            $identifier = $resource->attributes->getNamedItem('identifier');

            if (!is_null($identifier)) {
                $identifierValue = $identifier->nodeValue;

                if ($identifierValue === $identifierref) {
                    $href = $resource->attributes->getNamedItem('href');

                    if (is_null($href)) {
                        throw new InvalidScormArchiveException('sco_resource_without_href_message');
                    }

                    return $href->nodeValue;
                }
            }
        }

        throw new InvalidScormArchiveException('sco_without_resource_message');
    }
}
