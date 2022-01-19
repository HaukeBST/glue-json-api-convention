<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Glue\GlueJsonApiConvention;

use Spryker\Glue\GlueJsonApiConvention\Decoder\DecoderInterface;
use Spryker\Glue\GlueJsonApiConvention\Decoder\JsonDecoder;
use Spryker\Glue\GlueJsonApiConvention\Dependency\Service\GlueJsonApiConventionToUtilEncodingServiceInterface;
use Spryker\Glue\GlueJsonApiConvention\Encoder\EncoderInterface;
use Spryker\Glue\GlueJsonApiConvention\Encoder\JsonEncoder;
use Spryker\Glue\GlueJsonApiConvention\Request\AttributesRequestBuilder;
use Spryker\Glue\GlueJsonApiConvention\Request\RequestBuilderInterface;
use Spryker\Glue\GlueJsonApiConvention\Request\RequestRelationshipBuilder;
use Spryker\Glue\GlueJsonApiConvention\Request\RequestSparseFieldBuilder;
use Spryker\Glue\GlueJsonApiConvention\Request\RequestSparseFieldBuilderInterface;
use Spryker\Glue\GlueJsonApiConvention\Resource\JsonApiResourceExecutor;
use Spryker\Glue\GlueJsonApiConvention\Resource\JsonApiResourceExecutorInterface;
use Spryker\Glue\GlueJsonApiConvention\Resource\ResourceBuilder;
use Spryker\Glue\GlueJsonApiConvention\Resource\ResourceBuilderInterface;
use Spryker\Glue\GlueJsonApiConvention\Resource\ResourceExtractor;
use Spryker\Glue\GlueJsonApiConvention\Resource\ResourceExtractorInterface;
use Spryker\Glue\GlueJsonApiConvention\Resource\ResourceRelationshipLoader;
use Spryker\Glue\GlueJsonApiConvention\Resource\ResourceRelationshipLoaderInterface;
use Spryker\Glue\GlueJsonApiConvention\Response\JsonGlueResponseBuilder;
use Spryker\Glue\GlueJsonApiConvention\Response\JsonGlueResponseBuilderInterface;
use Spryker\Glue\GlueJsonApiConvention\Response\JsonGlueResponseFormatter;
use Spryker\Glue\GlueJsonApiConvention\Response\JsonGlueResponseFormatterInterface;
use Spryker\Glue\GlueJsonApiConvention\Response\RelationshipResponse;
use Spryker\Glue\GlueJsonApiConvention\Response\RelationshipResponseFormatter;
use Spryker\Glue\GlueJsonApiConvention\Response\RelationshipResponseFormatterInterface;
use Spryker\Glue\GlueJsonApiConvention\Response\RelationshipResponseInterface;
use Spryker\Glue\GlueJsonApiConvention\Router\RequestResourcePluginFilter;
use Spryker\Glue\GlueJsonApiConvention\Router\RequestResourcePluginFilterInterface;
use Spryker\Glue\GlueJsonApiConvention\Router\RequestRoutingMatcher;
use Spryker\Glue\GlueJsonApiConvention\Router\RequestRoutingMatcherInterface;
use Spryker\Glue\GlueJsonApiConventionExtension\Dependency\Plugin\ResourceRelationshipCollectionInterface;
use Spryker\Glue\Kernel\AbstractFactory;

/**
 * @method \Spryker\Glue\GlueJsonApiConvention\GlueJsonApiConventionConfig getConfig()
 */
class GlueJsonApiConventionFactory extends AbstractFactory
{
    /**
     * @return \Spryker\Glue\GlueJsonApiConvention\Request\RequestBuilderInterface
     */
    public function createRequestSparseFieldBuilder(): RequestBuilderInterface
    {
        return new RequestSparseFieldBuilder();
    }

    /**
     * @return \Spryker\Glue\GlueJsonApiConvention\Request\RequestBuilderInterface
     */
    public function createRequestRelationshipBuilder(): RequestBuilderInterface
    {
        return new RequestRelationshipBuilder();
    }

    /**
     * @return \Spryker\Glue\GlueJsonApiConvention\Decoder\DecoderInterface
     */
    public function createJsonDecoder(): DecoderInterface
    {
        return new JsonDecoder($this->getUtilEncodingService());
    }

    /**
     * @return \Spryker\Glue\GlueJsonApiConvention\Encoder\EncoderInterface
     */
    public function createJsonEncoder(): EncoderInterface
    {
        return new JsonEncoder($this->getUtilEncodingService());
    }

    /**
     * @return \Spryker\Glue\GlueJsonApiConvention\Response\JsonGlueResponseFormatterInterface
     */
    public function createJsonGlueResponseFormatter(): JsonGlueResponseFormatterInterface
    {
        return new JsonGlueResponseFormatter(
            $this->createJsonEncoder(),
            $this->getConfig(),
        );
    }

    /**
     * @return \Spryker\Glue\GlueJsonApiConvention\Response\JsonGlueResponseBuilderInterface
     */
    public function createJsonGlueResponseBuilder(): JsonGlueResponseBuilderInterface
    {
        return new JsonGlueResponseBuilder(
            $this->createJsonGlueResponseFormatter(),
            $this->getConfig(),
        );
    }

    /**
     * @return \Spryker\Glue\GlueJsonApiConvention\Response\RelationshipResponseFormatterInterface
     */
    public function createRelationshipResponseFormatter(): RelationshipResponseFormatterInterface
    {
        return new RelationshipResponseFormatter();
    }

    /**
     * @return \Spryker\Glue\GlueJsonApiConvention\Response\RelationshipResponseInterface
     */
    public function createRelationshipResponse(): RelationshipResponseInterface
    {
        return new RelationshipResponse($this->createResourceRelationshipLoader());
    }

    /**
     * @return \Spryker\Glue\GlueJsonApiConvention\Resource\ResourceRelationshipLoaderInterface
     */
    public function createResourceRelationshipLoader(): ResourceRelationshipLoaderInterface
    {
        return new ResourceRelationshipLoader($this->getRelationshipProviderPlugins());
    }

    /**
     * @return array<\Spryker\Glue\GlueJsonApiConventionExtension\Dependency\Plugin\ResourceRelationshipCollectionInterface>
     */
    public function getRelationshipProviderPlugins(): array
    {
        return $this->getProvidedDependency(GlueJsonApiConventionDependencyProvider::PLUGINS_RELATIONSHIP_PROVIDER);
    }

    /**
     * @return \Spryker\Glue\GlueJsonApiConvention\Dependency\Service\GlueJsonApiConventionToUtilEncodingServiceInterface
     */
    public function getUtilEncodingService(): GlueJsonApiConventionToUtilEncodingServiceInterface
    {
        return $this->getProvidedDependency(GlueJsonApiConventionDependencyProvider::SERVICE_UTIL_ENCODING);
    }

    /**
     * @return array<\Spryker\Glue\GlueJsonApiConventionExtension\Dependency\Plugin\RequestBuilderPluginInterface>
     */
    public function getRequestBuilderPlugins(): array
    {
        return $this->getProvidedDependency(GlueJsonApiConventionDependencyProvider::PLUGINS_REQUEST_BUILDER);
    }

    /**
     * @return array<\Spryker\Glue\GlueJsonApiConventionExtension\Dependency\Plugin\RequestValidatorPluginInterface>
     */
    public function getRequestValidatorPlugins(): array
    {
        return $this->getProvidedDependency(GlueJsonApiConventionDependencyProvider::PLUGINS_REQUEST_VALIDATOR);
    }

    /**
     * @return array<\Spryker\Glue\GlueJsonApiConventionExtension\Dependency\Plugin\RequestAfterRoutingValidatorPluginInterface>
     */
    public function getRequestAfterRoutingValidatorPlugins(): array
    {
        return $this->getProvidedDependency(GlueJsonApiConventionDependencyProvider::PLUGINS_REQUEST_AFTER_ROUTING_VALIDATOR);
    }

    /**
     * @return array<\Spryker\Glue\GlueJsonApiConventionExtension\Dependency\Plugin\ResponseFormatterPluginInterface>
     */
    public function getResponseFormatterPlugins(): array
    {
        return $this->getProvidedDependency(GlueJsonApiConventionDependencyProvider::PLUGINS_RESPONSE_FORMATTER);
    }

    /**
     * @return \Spryker\Glue\GlueJsonApiConvention\Request\RequestBuilderInterface
     */
    public function createAttributesRequestBuilder(): RequestBuilderInterface
    {
        return new AttributesRequestBuilder($this->createJsonDecoder());
    }
}
