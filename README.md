# SimpleApi by Marco Scholz

![PHP 8.2](https://img.shields.io/badge/PHP-8.2-8892BF.svg?style=flat-square)
![Build Status](https://github.com/marcoscholz/simple-api/workflows/Build/badge.svg)


**SimpleApi** is a minimalist, yet powerful standalone PHP framework designed to streamline the process of building robust APIs.

Crafted with simplicity in mind, it offers a clean and intuitive way to define routes, handle requests, and manage responses, all without relying on external dependencies. Whether you're building a small internal tool or a public-facing API, **SimpleApi** provides the essential tools to get the job done efficiently.

Check out the project on [GitHub](https://github.com/marcoscholz/simple-api).

## Features

- **Effortless Routing**
  - Use the `Router` class to easily direct incoming requests to the appropriate handlers.
- **Flexible Request Patterns**
  - Define and manage your API's request patterns with the `Request` class.
- **Utility-Packed Endpoints**
  - The `Endpoint` class offers a suite of utility methods for handling requests and crafting responses.
- **Custom Exception Handling**
  - Manage API-related errors gracefully with the `ApiException` class.
- **Zero Dependencies**
  - Built to function independently,
  - **SimpleApi** doesn't require any external packages, ensuring a lightweight footprint.

## Installation

[Provide installation instructions here, e.g., using Composer or direct download]

## Usage

1. **Define Your Routes**: Use the `Request` class to set up patterns for your API endpoints.
2. **Manage Incoming Traffic**: The `Router` class will guide incoming requests to the right destination.
3. **Handle & Respond**: Utilize the `Endpoint` class to process requests and send back appropriate responses.
4. **Error Management**: The `ApiException` class ensures that API-related errors are handled smoothly, providing clear feedback to the client.

## Examples

[Provide some basic examples of how to use the framework, perhaps a quick "Hello World" API endpoint]

```php
// Example code here
