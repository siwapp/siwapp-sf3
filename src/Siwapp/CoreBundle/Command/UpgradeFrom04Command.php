<?php

namespace Siwapp\CoreBundle\Command;

use Doctrine\ORM\EntityManager;
use Siwapp\ConfigBundle\Entity\Property;
use Siwapp\CoreBundle\Entity\AbstractInvoice;
use Siwapp\CoreBundle\Entity\Item;
use Siwapp\CoreBundle\Entity\Series;
use Siwapp\CoreBundle\Entity\Tax;
use Siwapp\InvoiceBundle\Entity\Invoice;
use Siwapp\InvoiceBundle\Entity\Payment;
use Siwapp\CustomerBundle\Entity\Customer;
use Siwapp\EstimateBundle\Entity\Estimate;
use Siwapp\ProductBundle\Entity\Product;
use Siwapp\RecurringInvoiceBundle\Entity\RecurringInvoice;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class UpgradeFrom04Command extends ContainerAwareCommand
{

    /**
     * Array mapping old ids to new.
     */
    protected $mapping;

    protected function configure()
    {
        $this
            ->setName('siwapp:upgrade-db:0.4-1.0')
            ->setDescription('Upgrade from v0.4')
            ->addArgument(
                'driver',
                InputArgument::REQUIRED,
                'The database driver eg. mysql'
            )
            ->addArgument(
                'username',
                InputArgument::REQUIRED,
                'The database username'
            )
            ->addArgument(
                'password',
                InputArgument::REQUIRED,
                'The database password'
            )
            ->addArgument(
                'db',
                InputArgument::REQUIRED,
                'The database name'
            )
            ->addArgument(
                'host',
                InputArgument::OPTIONAL,
                'The database host'
            )
            ->addArgument(
                'port',
                InputArgument::OPTIONAL,
                'The database port'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $options = [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION, \PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'];
        $dsn = sprintf('%s:host=%s;port=%s;dbname=%s',
            $input->getArgument('driver', 'mysql'),
            $input->getArgument('host', 'localhost'),
            $input->getArgument('port', 3306),
            $input->getArgument('db')
        );
        $dbh = new \PDO($dsn, $input->getArgument('username'), $input->getArgument('password'), $options);
        $em = $this->getContainer()->get('doctrine')->getManager();

        $types = ['Properties', 'Series', 'Taxes', 'Customers', 'Products', 'Estimates', 'RecurringInvoices', 'Invoices', 'Templates'];
        foreach ($types as $type) {
            $count = $this->{'import' . $type}($dbh, $em);
            $output->writeln(sprintf('Imported %d %s', $count, $type));
        }
        $output->writeln('You may have to perform additional actions in order to correctly port your templates.');
        $output->writeln('You must copy your logo from the previous Siwapp instalation to "web/uploads".');

        $em->flush();
    }

    protected function importProperties(\PDO $dbh, EntityManager $em)
    {
        $count = 0;
        $sth = $dbh->prepare('SELECT * FROM property');
        $sth->execute();
        $repo = $em->getRepository('SiwappConfigBundle:Property');
        foreach ($sth->fetchAll(\PDO::FETCH_ASSOC) as $row) {
            $property = $repo->find($row['keey']);
            if (!$property) {
                $property = new Property;
                $property->setKey($row['keey']);
            }
            $property->setValue(json_decode($row['value']));
            $em->persist($property);
            $count++;
        }

        return $count;
    }

    protected function importSeries(\PDO $dbh, EntityManager $em)
    {
        $count = 0;
        $sth = $dbh->prepare('SELECT * FROM series');
        $sth->execute();
        foreach ($sth->fetchAll(\PDO::FETCH_ASSOC) as $row) {
            $series = new Series;
            $series->setName($row['name']);
            $series->setValue($row['value']);
            $series->setFirstNumber($row['first_number']);
            $series->setEnabled($row['enabled']);
            $em->persist($series);
            $this->mapping['series'][$row['id']] = $series;
            $count++;
        }

        return $count;
    }

    protected function importTaxes(\PDO $dbh, EntityManager $em)
    {
        $count = 0;
        $sth = $dbh->prepare('SELECT * FROM tax');
        $sth->execute();
        foreach ($sth->fetchAll(\PDO::FETCH_ASSOC) as $row) {
            $tax = new Tax;
            $tax->setName($row['name']);
            $tax->setValue($row['value']);
            $tax->setActive($row['active']);
            $tax->setIsDefault($row['is_default']);
            $em->persist($tax);
            $this->mapping['taxes'][$row['id']] = $tax;
            $count++;
        }

        return $count;
    }

    protected function importCustomers(\PDO $dbh, EntityManager $em)
    {
        $count = 0;
        $sth = $dbh->prepare('SELECT * FROM customer');
        $sth->execute();
        foreach ($sth->fetchAll(\PDO::FETCH_ASSOC) as $row) {
            if (empty($row['name']) && empty($row['email'])) {
                // If there is no name and email available, then its trash; skip it.
                continue;
            }

            if (empty($row['name'])) {
                // In previous version name was optional.
                $name = 'Upgradefrom04 Default Name';
            } else {
                $name = $row['name'];
            }
            $customer = new Customer;
            $customer->setName($name);
            if ($row['identification'] && $row['identification'] !== 'Client Legal Id') {
                $customer->setIdentification($row['identification']);
            }
            $email = $row['email'];
            // In previous version email was optional.
            if (!$email) {
                $email = strtolower(str_replace(' ', '-', $name)) . '@upgradefrom04.fixme';
            }
            $customer->setEmail($email);
            $customer->setContactPerson($row['contact_person']);
            $customer->setInvoicingAddress($row['invoicing_address']);
            $customer->setShippingAddress($row['shipping_address']);
            $em->persist($customer);
            $count++;
        }

        return $count;
    }

    protected function importProducts(\PDO $dbh, EntityManager $em)
    {
        $count = 0;
        $sth = $dbh->prepare('SELECT * FROM product');
        $sth->execute();
        foreach ($sth->fetchAll(\PDO::FETCH_ASSOC) as $row) {
            $product = new Product;
            $product->setReference($row['reference']);
            $product->setDescription($row['description']);
            $product->setPrice($row['price']);
            $product->setCreatedAt(new \DateTime($row['created_at']));
            $product->setUpdatedAt(new \DateTime($row['updated_at']));
            $em->persist($product);
            $this->mapping['products'][$row['id']] = $product;
            $count++;
        }

        return $count;
    }

    protected function importEstimates(\PDO $dbh, EntityManager $em)
    {
        $count = 0;
        $sth = $dbh->prepare("SELECT * FROM common WHERE type='Estimate'");
        $sth->execute();
        foreach ($sth->fetchAll(\PDO::FETCH_ASSOC) as $row) {
            $estimate = new Estimate($row);
            $this->setCommonInvoiceProperties($estimate, $row);
            // Status mapping.
            $status = $row['status'];
            if ($status == 3) {
                $status = 1;
            }
            elseif ($status > 0) {
                $status--;
            }
            $estimate->setStatus($status);
            $estimate->setNumber($row['number']);
            $estimate->setSentByEmail($row['sent_by_email']);
            $estimate->setIssueDate($row['issue_date']);

            $this->addItemsAndTaxes($estimate, $row, $dbh);

            $em->persist($estimate);
            $count++;
        }

        return $count;
    }

    protected function importRecurringInvoices(\PDO $dbh, EntityManager $em)
    {
        $count = 0;
        $sth = $dbh->prepare("SELECT * FROM common WHERE type='RecurringInvoice'");
        $sth->execute();
        foreach ($sth->fetchAll(\PDO::FETCH_ASSOC) as $row) {
            $recurring = new RecurringInvoice($row);
            $this->setCommonInvoiceProperties($recurring, $row);
            $recurring->setStatus($row['status']);
            $recurring->setDaysToDue($row['days_to_due']);
            $recurring->setEnabled($row['enabled']);
            $recurring->setMaxOccurrences($row['max_occurrences']);
            $recurring->setPeriod($row['period']);
            $recurring->setPeriodType($row['period_type']);
            $recurring->setStartingDate($row['starting_date']);
            $recurring->setFinishingDate($row['finishing_date']);
            $recurring->setLastExecutionDate($row['last_execution_date']);

            $this->addItemsAndTaxes($recurring, $row, $dbh);

            $em->persist($recurring);
            $count++;
        }

        return $count;
    }

    protected function importInvoices(\PDO $dbh, EntityManager $em)
    {
        $count = 0;
        $sth = $dbh->prepare("SELECT * FROM common WHERE type='Invoice'");
        $sth->execute();
        foreach ($sth->fetchAll(\PDO::FETCH_ASSOC) as $row) {
            $invoice = new Invoice($row);
            $this->setCommonInvoiceProperties($invoice, $row);

            $invoice->setNumber($row['number']);
            $invoice->setStatus($row['status']);
            $invoice->setSentByEmail($row['sent_by_email']);
            $invoice->setIssueDate($row['issue_date']);
            $invoice->setDueDate($row['due_date']);

            $this->addItemsAndTaxes($invoice, $row, $dbh);

            $paymentsSth = $dbh->prepare('SELECT * FROM payment WHERE invoice_id=:id');
            $paymentsSth->bindValue(':id', $row['id']);
            $paymentsSth->execute();
            foreach ($paymentsSth->fetchAll(\PDO::FETCH_ASSOC) as $paymentRow) {
                if (!$paymentRow['amount']) {
                    continue;
                }
                $payment = new Payment;
                $payment->setDate($paymentRow['date']);
                $payment->setAmount($paymentRow['amount']);
                $payment->setNotes($paymentRow['notes']);
                $invoice->addPayment($payment);
            }

            $em->persist($invoice);
            $count++;
        }

        return $count;
    }

    protected function importTemplates(\PDO $dbh)
    {
        $count = 0;
        $sth = $dbh->prepare('SELECT * FROM template');
        $sth->execute();
        $path = $this->getContainer()->getParameter('kernel.root_dir');
        $estimateViewsDir = $path . '/Resources/SiwappEstimateBundle/views/Estimate';
        $invoiceViewsDir = $path . '/Resources/SiwappInvoiceBundle/views/Invoice';
        if (!file_exists($estimateViewsDir)) {
            mkdir($estimateViewsDir, 0755, true);
        }
        if (!file_exists($invoiceViewsDir)) {
            mkdir($invoiceViewsDir, 0755, true);
        }
        foreach ($sth->fetchAll(\PDO::FETCH_ASSOC) as $row) {
            if (!$row['models']) {
                continue;
            }
            $template = $row['template'];
            $template = str_replace('|currency', '|localizedcurrency', $template);
            $template = str_replace(['{{lang}}', '{{ lang }}'], '{{ app.request.locale }}', $template);
            if (strpos($row['models'], 'Estimate') !== false) {
                file_put_contents($estimateViewsDir . '/print.html.twig', $template);
            }
            if (strpos($row['models'], 'Invoice') !== false) {
                file_put_contents($invoiceViewsDir . '/print.html.twig', $template);
            }
            $count++;
        }

        return $count;
    }

    protected function setCommonInvoiceProperties(AbstractInvoice $invoice, array $row)
    {
        if (!empty($row['series_id'])) {
            $invoice->setSeries($this->mapping['series'][$row['series_id']]);
        }
        $invoice->setCustomerName($row['customer_name']);
        $invoice->setCustomerIdentification($row['customer_identification']);
        $invoice->setCustomerEmail($row['customer_email']);
        $invoice->setInvoicingAddress($row['invoicing_address']);
        $invoice->setShippingAddress($row['shipping_address']);
        $invoice->setContactPerson($row['contact_person']);
        $invoice->setTerms($row['terms']);
        $invoice->setNotes($row['notes']);
        $invoice->setCreatedAt(new \DateTime($row['created_at']));
        $invoice->setUpdatedAt(new \DateTime($row['updated_at']));
    }

    protected function addItemsAndTaxes(AbstractInvoice $invoice, array $row, \PDO $dbh)
    {
        $itemsSth = $dbh->prepare('SELECT * FROM item WHERE common_id=:id');
        $itemsSth->bindValue(':id', $row['id']);
        $itemsSth->execute();
        foreach ($itemsSth->fetchAll(\PDO::FETCH_ASSOC) as $itemRow) {
            $item = new Item;
            $item->setDescription($itemRow['description']);
            $item->setUnitaryCost($itemRow['unitary_cost']);
            $item->setQuantity($itemRow['quantity']);
            $item->setDiscount($itemRow['discount']);
            if ($itemRow['product_id']) {
                $item->setProduct($this->mapping['products'][$itemRow['product_id']]);
            }

            $itemTaxesSth = $dbh->prepare('SELECT * FROM item_tax WHERE item_id=:id');
            $itemTaxesSth->bindValue(':id', $itemRow['id']);
            $itemTaxesSth->execute();
            foreach ($itemTaxesSth->fetchAll(\PDO::FETCH_ASSOC) as $itemTaxRow) {
                $item->addTax($this->mapping['taxes'][$itemTaxRow['tax_id']]);
            }

            $invoice->addItem($item);
        }
    }
}
