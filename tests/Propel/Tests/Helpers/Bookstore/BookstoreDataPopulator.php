<?php

/**
 * This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license MIT License
 */

namespace Propel\Tests\Helpers\Bookstore;

use Propel\Runtime\Configuration;

use Propel\Tests\Bookstore\AcctAccessRole;
use Propel\Tests\Bookstore\Author;
use Propel\Tests\Bookstore\Book;
use Propel\Tests\Bookstore\BookQuery;
use Propel\Tests\Bookstore\BookClubList;
use Propel\Tests\Bookstore\BookListRel;
use Propel\Tests\Bookstore\BookOpinion;
use Propel\Tests\Bookstore\BookReader;
use Propel\Tests\Bookstore\BookSummary;
use Propel\Tests\Bookstore\Bookstore;
use Propel\Tests\Bookstore\BookstoreEmployee;
use Propel\Tests\Bookstore\BookstoreEmployeeAccount;
use Propel\Tests\Bookstore\Media;
use Propel\Tests\Bookstore\Publisher;
use Propel\Tests\Bookstore\ReaderFavorite;
use Propel\Tests\Bookstore\Review;

use Propel\Tests\Bookstore\RecordLabel;
use Propel\Tests\Bookstore\ReleasePool;

define('_LOB_SAMPLE_FILE_PATH', __DIR__ . '/../../../../Fixtures/etc/lob');

/**
 * Populates data needed by the bookstore unit tests.
 *
 * This classes uses the actual Propel objects to do the population rather than
 * inserting directly into the database.  This will have a performance hit, but will
 * benefit from increased flexibility (as does anything using Propel).
 *
 * @author Hans Lellelid <hans@xmpl.org>
 */
class BookstoreDataPopulator
{

    public static function populate()
    {
        // Add publisher records
        // ---------------------

        $scholastic = new Publisher();
        $scholastic->setName("Scholastic");
        // do not save, will do later to test cascade

        $morrow = new Publisher();
        $morrow->setName("William Morrow");
        $morrow->save();
        $morrow_id = $morrow->getId();

        $penguin = new Publisher();
        $penguin->setName("Penguin");
        $penguin->save();
        $penguin_id = $penguin->getId();

        $vintage = new Publisher();
        $vintage->setName("Vintage");
        $vintage->save();
        $vintage_id = $vintage->getId();

        $rowling = new Author();
        $rowling->setFirstName("J.K.");
        $rowling->setLastName("Rowling");
        // no save()

        $stephenson = new Author();
        $stephenson->setFirstName("Neal");
        $stephenson->setLastName("Stephenson");
        $stephenson->save();
        $stephenson_id = $stephenson->getId();

        $byron = new Author();
        $byron->setFirstName("George");
        $byron->setLastName("Byron");
        $byron->save();
        $byron_id = $byron->getId();

        $grass = new Author();
        $grass->setFirstName("Gunter");
        $grass->setLastName("Grass");
        $grass->save();
        $grass_id = $grass->getId();

        $phoenix = new Book();
        $phoenix->setTitle("Harry Potter and the Order of the Phoenix");
        $phoenix->setISBN("043935806X");
        $phoenix->setAuthor($rowling);
        $phoenix->setPublisher($scholastic);
        $phoenix->setPrice(10.99);
        $phoenix->save();
        $phoenix_id = $phoenix->getId();

        $qs = new Book();
        $qs->setISBN("0380977427");
        $qs->setTitle("Quicksilver");
        $qs->setPrice(11.99);
        $qs->setAuthor($stephenson);
        $qs->setPublisher($morrow);
        $qs->save();
        $qs_id = $qs->getId();

        $dj = new Book();
        $dj->setISBN("0140422161");
        $dj->setTitle("Don Juan");
        $dj->setPrice(12.99);
        $dj->setAuthor($byron);
        $dj->setPublisher($penguin);
        $dj->save();
        $dj_id = $dj->getId();

        $td = new Book();
        $td->setISBN("067972575X");
        $td->setTitle("The Tin Drum");
        $td->setPrice(13.99);
        $td->setAuthor($grass);
        $td->setPublisher($vintage);
        $td->save();
        $td_id = $td->getId();

        $r1 = new Review();
        $r1->setBook($phoenix);
        $r1->setReviewedBy("Washington Post");
        $r1->setRecommended(true);
        $r1->setReviewDate(time());
        $r1->save();
        $r1_id = $r1->getId();

        $r2 = new Review();
        $r2->setBook($phoenix);
        $r2->setReviewedBy("New York Times");
        $r2->setRecommended(false);
        $r2->setReviewDate(time());
        $r2->save();
        $r2_id = $r2->getId();

        $blob_path = _LOB_SAMPLE_FILE_PATH . '/tin_drum.gif';
        $clob_path =  _LOB_SAMPLE_FILE_PATH . '/tin_drum.txt';

        $m1 = new Media();
        $m1->setBook($td);
        $m1->setCoverImage(file_get_contents($blob_path));
        // CLOB is broken in PDO OCI, see http://pecl.php.net/bugs/bug.php?id=7943
        if (get_class(Configuration::getCurrentConfiguration()->getAdapter('bookstore')) != "OracleAdapter") {
            $m1->setExcerpt(file_get_contents($clob_path));
        }
        $m1->save();

        // Add book list records
        // ---------------------
        // (this is for many-to-many tests)

        $blc1 = new BookClubList();
        $blc1->setGroupLeader("Crazyleggs");
        $blc1->setTheme("Happiness");

        $brel1 = new BookListRel();
        $brel1->setBook($phoenix);

        $brel2 = new BookListRel();
        $brel2->setBook($dj);

        $blc1->addBookListRel($brel1);
        $blc1->addBookListRel($brel2);

        $blc1->save();

        $bemp1 = new BookstoreEmployee();
        $bemp1->setName("John");
        $bemp1->setJobTitle("Manager");

        $bemp2 = new BookstoreEmployee();
        $bemp2->setName("Pieter");
        $bemp2->setJobTitle("Clerk");
        $bemp2->setSupervisor($bemp1);
        $bemp2->save();

        $role = new AcctAccessRole();
        $role->setName("Admin");

        $bempacct = new BookstoreEmployeeAccount();
        $bempacct->setBookstoreEmployee($bemp1);
        $bempacct->setAcctAccessRole($role);
        $bempacct->setLogin("john");
        $bempacct->setPassword("johnp4ss");
        $bempacct->save();

        // Add bookstores

        $store = new Bookstore();
        $store->setStoreName("Amazon");
        $store->setPopulationServed(5000000000); // world population
        $store->setTotalBooks(300);
        $store->save();

        $store = new Bookstore();
        $store->setStoreName("Local Store");
        $store->setPopulationServed(20);
        $store->setTotalBooks(500000);
        $store->save();

        $summary = new BookSummary();
        $summary->setSummarizedBook($phoenix);
        $summary->setSummary("Harry Potter does some amazing magic!");
        $summary->save();

        // Add release_pool and record_label
        $acuna = new RecordLabel();
        $acuna->setAbbr('acuna');
        $acuna->setName('Acunadeep');
        $acuna->save();

        $fade = new RecordLabel();
        $fade->setAbbr('fade');
        $fade->setName('Fade Records');
        $fade->save();

        $pool = new ReleasePool();
        $pool->setName('D.Chmelyuk - Revert Me Back');
        $pool->setRecordLabel($acuna);
        $pool->save();

        $pool = new ReleasePool();
        $pool->setName('VIF & Lola Palmer - Dreamer');
        $pool->setRecordLabel($acuna);
        $pool->save();

        $pool = new ReleasePool();
        $pool->setName('Lola Palmer - Do You Belong To Me');
        $pool->setRecordLabel($acuna);
        $pool->save();

        $pool = new ReleasePool();
        $pool->setName('Chris Forties - Despegue (foem.info Runners Up Remixes)');
        $pool->setRecordLabel($fade);
        $pool->save();
    }

    public static function populateOpinionFavorite()
    {
        $book1 = BookQuery::create()->findOne();
        $reader1 = new BookReader();
        $reader1->save();

        $bo = new BookOpinion();
        $bo->setBook($book1);
        $bo->setBookReader($reader1);
        $bo->save();

        $rf = new ReaderFavorite();
        $rf->setBookOpinion($bo);
        $rf->save();
    }

    public static function depopulate(Configuration $configuration)
    {
        $entityClasses = array(
            'Propel\Tests\Bookstore\Author',
            'Propel\Tests\Bookstore\Bookstore',
            'Propel\Tests\Bookstore\BookstoreContest',
            'Propel\Tests\Bookstore\BookstoreContestEntry',
            'Propel\Tests\Bookstore\BookstoreEmployee',
            'Propel\Tests\Bookstore\BookstoreEmployeeAccount',
            'Propel\Tests\Bookstore\BookstoreSale',
            'Propel\Tests\Bookstore\BookClubList',
            'Propel\Tests\Bookstore\BookOpinion',
            'Propel\Tests\Bookstore\BookReader',
            'Propel\Tests\Bookstore\BookListRel',
            'Propel\Tests\Bookstore\Book',
            'Propel\Tests\Bookstore\Contest',
            'Propel\Tests\Bookstore\Customer',
            'Propel\Tests\Bookstore\Media',
            'Propel\Tests\Bookstore\Publisher',
            'Propel\Tests\Bookstore\ReaderFavorite',
            'Propel\Tests\Bookstore\Review',
            'Propel\Tests\Bookstore\BookSummary',
            'Propel\Tests\Bookstore\RecordLabel',
            'Propel\Tests\Bookstore\ReleasePool',
        );

        // free the memory from existing objects
        foreach ($entityClasses as $entityClass) {
            $configuration
                ->getRepository($entityClass)
                ->deleteAll();
        }
    }

}
