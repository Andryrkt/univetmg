<?php

namespace App\Tests\Controller\Admin;

use App\Entity\Admin\Fournisseur;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class FournisseurControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $manager;
    private EntityRepository $fournisseurRepository;
    private string $path = '/admin/fournisseur/';

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->manager = static::getContainer()->get('doctrine')->getManager();
        $this->fournisseurRepository = $this->manager->getRepository(Fournisseur::class);

        foreach ($this->fournisseurRepository->findAll() as $object) {
            $this->manager->remove($object);
        }

        $this->manager->flush();
    }

    public function testIndex(): void
    {
        $this->client->followRedirects();
        $crawler = $this->client->request('GET', $this->path);

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Fournisseur index');

        // Use the $crawler to perform additional assertions e.g.
        // self::assertSame('Some text on the page', $crawler->filter('.p')->first()->text());
    }

    public function testNew(): void
    {
        $this->markTestIncomplete();
        $this->client->request('GET', sprintf('%snew', $this->path));

        self::assertResponseStatusCodeSame(200);

        $this->client->submitForm('Save', [
            'fournisseur[nom]' => 'Testing',
            'fournisseur[telephone]' => 'Testing',
            'fournisseur[adresse]' => 'Testing',
            'fournisseur[email]' => 'Testing',
        ]);

        self::assertResponseRedirects($this->path);

        self::assertSame(1, $this->fournisseurRepository->count([]));
    }

    public function testShow(): void
    {
        $this->markTestIncomplete();
        $fixture = new Fournisseur();
        $fixture->setNom('My Title');
        $fixture->setTelephone('My Title');
        $fixture->setAdresse('My Title');
        $fixture->setEmail('My Title');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Fournisseur');

        // Use assertions to check that the properties are properly displayed.
    }

    public function testEdit(): void
    {
        $this->markTestIncomplete();
        $fixture = new Fournisseur();
        $fixture->setNom('Value');
        $fixture->setTelephone('Value');
        $fixture->setAdresse('Value');
        $fixture->setEmail('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $fixture->getId()));

        $this->client->submitForm('Update', [
            'fournisseur[nom]' => 'Something New',
            'fournisseur[telephone]' => 'Something New',
            'fournisseur[adresse]' => 'Something New',
            'fournisseur[email]' => 'Something New',
        ]);

        self::assertResponseRedirects('/admin/fournisseur/');

        $fixture = $this->fournisseurRepository->findAll();

        self::assertSame('Something New', $fixture[0]->getNom());
        self::assertSame('Something New', $fixture[0]->getTelephone());
        self::assertSame('Something New', $fixture[0]->getAdresse());
        self::assertSame('Something New', $fixture[0]->getEmail());
    }

    public function testRemove(): void
    {
        $this->markTestIncomplete();
        $fixture = new Fournisseur();
        $fixture->setNom('Value');
        $fixture->setTelephone('Value');
        $fixture->setAdresse('Value');
        $fixture->setEmail('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));
        $this->client->submitForm('Delete');

        self::assertResponseRedirects('/admin/fournisseur/');
        self::assertSame(0, $this->fournisseurRepository->count([]));
    }
}
