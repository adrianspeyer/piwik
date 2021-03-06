<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

use \Piwik\CronArchive\SharedSiteIds;

/**
 * @group Core
 */
class SharedSiteIdsTest extends DatabaseTestCase
{
    /**
     * @var SharedSiteIds
     */
    private $sharedSiteIds;

    public function setUp()
    {
        parent::setUp();
        $this->fixture->performSetUp(null, true);

        $this->sharedSiteIds = new SharedSiteIds(array(1,2,5,9));
    }

    public function tearDown()
    {
        $siteIdsToCleanup = new SharedSiteIds(array());
        $siteIdsToCleanup->setSiteIdsToArchive(array());

        parent::tearDown();
    }

    public function test_construct_withEmptyValue()
    {
        $this->sharedSiteIds->setSiteIdsToArchive(array());

        $siteIds = new SharedSiteIds(null);
        $this->assertEquals(0, $siteIds->getNumSites());
        $this->assertNull($siteIds->getNextSiteId());
    }

    public function test_isSupported()
    {
        $this->assertTrue(SharedSiteIds::isSupported());
    }

    public function test_getNumSites()
    {
        $this->assertEquals(4, $this->sharedSiteIds->getNumSites());
    }

    public function test_getAllSiteIdsToArchive()
    {
        $this->assertEquals(array(1,2,5,9), $this->sharedSiteIds->getAllSiteIdsToArchive());
    }

    public function test_getNumProcessedWebsites_getNextSiteId()
    {
        $this->assertEquals(0, $this->sharedSiteIds->getNumProcessedWebsites());

        $this->assertEquals(1, $this->sharedSiteIds->getNextSiteId());
        $this->assertEquals(1, $this->sharedSiteIds->getNumProcessedWebsites());

        $this->assertEquals(2, $this->sharedSiteIds->getNextSiteId());
        $this->assertEquals(2, $this->sharedSiteIds->getNumProcessedWebsites());

        $this->assertEquals(5, $this->sharedSiteIds->getNextSiteId());
        $this->assertEquals(3, $this->sharedSiteIds->getNumProcessedWebsites());

        $this->assertEquals(9, $this->sharedSiteIds->getNextSiteId());
        $this->assertEquals(4, $this->sharedSiteIds->getNumProcessedWebsites());

        $this->assertNull($this->sharedSiteIds->getNextSiteId());
        $this->assertEquals(4, $this->sharedSiteIds->getNumProcessedWebsites());
    }

    public function test_usingMultipleSharedSiteIds()
    {
        $second = new SharedSiteIds(array(7,9,11,6,1,2));

        // should ignore his queue and help processing the existing queue
        $this->assertEquals(4, $second->getNumSites());
        $this->assertEquals(4, $this->sharedSiteIds->getNumSites());

        $this->assertEquals(array(1,2,5,9), $second->getAllSiteIdsToArchive());
        $this->assertEquals(1, $second->getNextSiteId());
        $this->assertEquals(1, $second->getNumProcessedWebsites());

        $this->assertEquals(array(2,5,9), $this->sharedSiteIds->getAllSiteIdsToArchive());
        $this->assertEquals(2, $this->sharedSiteIds->getNextSiteId());
        $this->assertEquals(2, $this->sharedSiteIds->getNumProcessedWebsites());

        $this->assertEquals(array(5,9), $second->getAllSiteIdsToArchive());
        $this->assertEquals(5, $second->getNextSiteId());
        $this->assertEquals(3, $second->getNumProcessedWebsites());

        $this->assertEquals(array(9), $this->sharedSiteIds->getAllSiteIdsToArchive());
        $this->assertEquals(9, $this->sharedSiteIds->getNextSiteId());
        $this->assertEquals(4, $this->sharedSiteIds->getNumProcessedWebsites());

        $this->assertNull($second->getNextSiteId());
        $this->assertEquals(4, $second->getNumProcessedWebsites());
        $this->assertEquals(array(), $second->getAllSiteIdsToArchive());

        $this->assertNull($this->sharedSiteIds->getNextSiteId());
        $this->assertEquals(4, $this->sharedSiteIds->getNumProcessedWebsites());
        $this->assertEquals(array(), $this->sharedSiteIds->getAllSiteIdsToArchive());
    }

}