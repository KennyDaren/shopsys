<?php

namespace SS6\ShopBundle\Form;

use SS6\ShopBundle\Component\Constraints\UniqueSlugsOnDomains;
use SS6\ShopBundle\Component\Domain\Domain;
use SS6\ShopBundle\Component\Router\DomainRouterFactory;
use SS6\ShopBundle\Component\Router\FriendlyUrl\FriendlyUrl;
use SS6\ShopBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\ChoiceList\ObjectChoiceList;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints;

class UrlListType extends AbstractType {

	const SLUG_REGEX = '/^[\w_\-\/]+$/';

	const TO_DELETE = 'toDelete';
	const MAIN_ON_DOMAINS = 'mainOnDomains';
	const NEW_SLUGS_ON_DOMAINS = 'newSlugsOnDomains';

	/**
	 * @var \Symfony\Component\Form\FormFactoryInterface
	 */
	private $formFactory;

	/**
	 * @var \SS6\ShopBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade
	 */
	private $friendlyUrlFacade;

	/**
	 * @var \SS6\ShopBundle\Component\Router\DomainRouterFactory
	 */
	private $domainRouterFactory;

	/**
	 * @var \SS6\ShopBundle\Component\Domain\Domain
	 */
	private $domain;

	public function __construct(
		FormFactoryInterface $formFactory,
		FriendlyUrlFacade $friendlyUrlFacade,
		DomainRouterFactory $domainRouterFactory,
		Domain $domain
	) {
		$this->friendlyUrlFacade = $friendlyUrlFacade;
		$this->domainRouterFactory = $domainRouterFactory;
		$this->domain = $domain;
		$this->formFactory = $formFactory;
	}

	/**
	 * @param \Symfony\Component\Form\FormBuilderInterface $builder
	 * @param array $options
	 */
	public function buildForm(FormBuilderInterface $builder, array $options) {
		if ($options['route_name'] === null) {
			throw new \SS6\ShopBundle\Form\Exception\MissingRouteNameException();
		}

		$builder->add(self::TO_DELETE, FormType::FORM);
		$builder->add(self::MAIN_ON_DOMAINS, FormType::FORM);
		$builder->add(
			self::NEW_SLUGS_ON_DOMAINS,
			FormType::FORM,
			[
				'constraints' => [
					new UniqueSlugsOnDomains(),
				],
				'error_bubbling' => false,
			]
		);

		$friendlyUrlsByDomain = $this->getFriendlyUrlsIndexedByDomain($options['route_name'], $options['entity_id']);

		foreach ($friendlyUrlsByDomain as $domainId => $friendlyUrls) {
			$builder->get(self::TO_DELETE)->add($domainId, FormType::CHOICE, [
				'required' => false,
				'multiple' => true,
				'expanded' => true,
				'choice_list' => new ObjectChoiceList($friendlyUrls, 'slug', [], null, 'slug'),
			]);
			$builder->get(self::MAIN_ON_DOMAINS)->add($domainId, FormType::CHOICE, [
				'required' => true,
				'multiple' => false,
				'expanded' => true,
				'choice_list' => new ObjectChoiceList($friendlyUrls, 'slug', [], null, 'slug'),
				'data_class' => FriendlyUrl::class,
				'invalid_message' => 'Původně vybraná hlavní URL již neexistuje',
			]);
			$builder->get(self::NEW_SLUGS_ON_DOMAINS)->add($domainId, FormType::COLLECTION, [
				'type' => FormType::HIDDEN,
				'required' => false,
				'allow_add' => true,
				'constraints' => [

				],
				'options' => [
					'constraints' => [
						new Constraints\NotBlank(),
						new Constraints\Regex([
							'pattern' => self::SLUG_REGEX,
							'message' => 'Url {{ value }} obsahuje nepovolené znaky.',
						]),
					],
				],
			]);
		}
	}

	/**
	 * @param \Symfony\Component\Form\FormView $view
	 * @param \Symfony\Component\Form\FormInterface $form
	 * @param array $options
	 */
	public function buildView(FormView $view, FormInterface $form, array $options) {
		$absoluteUrlsByDomainIdAndSlug = $this->getAbsoluteUrlsIndexedByDomainIdAndSlug(
			$options['route_name'],
			$options['entity_id']
		);
		$mainUrlsSlugsOnDomains = $this->getMainFriendlyUrlSlugsByDomainId(
			$options['route_name'],
			$options['entity_id']
		);

		$view->vars['absoluteUrlsByDomainIdAndSlug'] = $absoluteUrlsByDomainIdAndSlug;
		$view->vars['routeName'] = $options['route_name'];
		$view->vars['entityId'] = $options['entity_id'];
		$view->vars['mainUrlsSlugsOnDomains'] = $mainUrlsSlugsOnDomains;
		$view->vars['domainUrlsById'] = $this->getDomainUrlsIndexedById();
	}

	/**
	 * @param \Symfony\Component\OptionsResolver\OptionsResolverInterface $resolver
	 */
	public function setDefaultOptions(OptionsResolverInterface $resolver) {
		$resolver->setDefaults([
			'required' => false,
			'route_name' => null,
			'entity_id' => null,
		]);
	}

	/**
	 * @return string
	 */
	public function getParent() {
		return 'form';
	}

	/**
	 * @return string
	 */
	public function getName() {
		return 'url_list';
	}

	/**
	 * @param string $routeName
	 * @param string $entityId
	 * @return \SS6\ShopBundle\Component\Router\FriendlyUrl\FriendlyUrl[domainId][]
	 */
	private function getFriendlyUrlsIndexedByDomain($routeName, $entityId) {
		$friendlyUrlsByDomain = [];
		if ($entityId !== null) {
			$friendlyUrls = $this->friendlyUrlFacade->getAllByRouteNameAndEntityId($routeName, $entityId);
			foreach ($friendlyUrls as $friendlyUrl) {
				$friendlyUrlsByDomain[$friendlyUrl->getDomainId()][] = $friendlyUrl;
			}
		}

		return $friendlyUrlsByDomain;
	}

	/**
	 * @param string $routeName
	 * @param string $entityId
	 * @return string[domainId][slug]
	 */
	private function getAbsoluteUrlsIndexedByDomainIdAndSlug($routeName, $entityId) {
		$friendlyUrlsByDomain = $this->getFriendlyUrlsIndexedByDomain($routeName, $entityId);
		$absoluteUrlsByDomainIdAndSlug = [];
		foreach ($friendlyUrlsByDomain as $domainId => $friendlyUrls) {
			$domainRouter = $this->domainRouterFactory->getRouter($domainId);
			$absoluteUrlsByDomainIdAndSlug[$domainId] = [];
			foreach ($friendlyUrls as $friendlyUrl) {
				$absoluteUrlsByDomainIdAndSlug[$domainId][$friendlyUrl->getSlug()] =
					$domainRouter->generateByFriendlyUrl(
						$friendlyUrl,
						[],
						Router::ABSOLUTE_URL
					);
			}
		}

		return $absoluteUrlsByDomainIdAndSlug;
	}

	/**
	 * @param string $routeName
	 * @param int $entityId
	 * @return string[domainId]
	 */
	private function getMainFriendlyUrlSlugsByDomainId($routeName, $entityId) {
		$mainFriendlyUrlsSlugsOnDomains = [];
		foreach ($this->domain->getAll() as $domainConfig) {
			$domainId = $domainConfig->getId();
			$mainFriendlyUrl = $this->friendlyUrlFacade->findMainFriendlyUrl(
				$domainId,
				$routeName,
				$entityId
			);
			if ($mainFriendlyUrl !== null) {
				$mainFriendlyUrlsSlugsOnDomains[$domainId] = $mainFriendlyUrl->getSlug();
			} else {
				$mainFriendlyUrlsSlugsOnDomains[$domainId] = null;
			}
		}

		return $mainFriendlyUrlsSlugsOnDomains;
	}

	/**
	 * @return string[domainId]
	 */
	private function getDomainUrlsIndexedById() {
		$domainUrlsById = [];
		foreach ($this->domain->getAll() as $domainConfig) {
			$domainUrlsById[$domainConfig->getId()] = $domainConfig->getUrl();
		}

		return $domainUrlsById;
	}

}
