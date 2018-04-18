# PHP Integration Engineer Test

## The Problem

We (the shipping team) released a new service that provides a RESTful API to replace the direct access to the CEPs(1) database in order to auto-complete the address form in our checkout. Now, you are responsible to change the current code base and replace the old implementation by a new one, using the available resources in the API.

## The Objective

Given the existing code base, and the available API resources described in this above, write a new implementation to access the provided service.

## Business Requirements

1 - The current implementation is not good enough and we know that. As you are improving this feature, we expect that you can improve the software design as well, so, creating new entities, optimising statements and writing new tests are a good start point.

2 - This service is new and never went to production before, so to minimize the risks, we want to release it only for the stores that are a part of the beta testing program, and all the remaining stores should use the old implementation. Check out the Store model to figure out how to determine if a store matches the condition.

3 - And, to avoid crawlers and bots, the service has a limit of 10k requests/hour, but in some hours of the day we have peaks of more than 10k requests/hour and you should consider it when implementing the solution.

4 - Finally, an error (of any type) should never be shown to the users, so, error treatment is required in a end-to-end way.

## The API

The Address API has only one endpoint and it was designed to provide resilience and scalability in some parts of our platform.

### Base URL

The specified service is available under: `https://shipping.tiendanube.com/v1/`

### Address Endpoint

The Address API is available in the *address* endpoint the has the following format, where *{0}* is the zipcode of requested address: `/address/{0}`.

Here are some examples of the expected behaviors:

#### Available Address 
```
$ curl -XGET -H 'Authentication bearer: YouShallNotPass' -H "Content-type: application/json" https://shipping.tiendanube.com/address/40010000

HTTP/1.1 200 OK
Server: nginx/1.12.2
Content-Type: application/json
Content-Length: 308
X-Rate-Limit: 10000
X-Rate-Remaining: 8790
X-Rate-Reset: 8241

{
    "altitude":7.0,
    "cep":"40010000",
    "latitude":"-12.967192",
    "longitude":"-38.5101976",
    "address":"Avenida da França",
    "neighborhood":"Comércio",
    "city":{  
        "ddd":71,
        "ibge":"2927408",
        "name":"Salvador"
    },
    "state":{  
        "acronym":"BA"
    }
}
```

#### Nonexistent Address
```
$ curl -XGET -H 'Authentication bearer: YouShallNotPass' -H "Content-type: application/json" https://shipping.tiendanube.com/address/400100001

HTTP/1.1 404 Not Found
Server: nginx/1.12.2
Content-Type: application/json
Content-Length: 0
X-Rate-Limit: 10000
X-Rate-Remaining: 8789
X-Rate-Reset: 6712
```

#### Server Error
```
$ curl -XGET -H 'Authentication bearer: YouShallNotPass' -H "Content-type: application/json" https://shipping.tiendanube.com/address/40010000

HTTP/1.1 500 Internal Server Error
Server: nginx/1.12.2
Content-Type: application/json
Content-Length: 0
X-Rate-Limit: 10000
X-Rate-Remaining: 8788
X-Rate-Reset: 4852
```

#### Service Usage Exceeded 
```
$ curl -XGET -H 'Authentication bearer: YouShallNotPass' -H "Content-type: application/json" https://shipping.tiendanube.com/address/40010000

HTTP/1.1 429 Too Many Requests
Server: nginx/1.12.2
Content-Type: application/json
Content-Length: 76
X-Rate-Limit: 10000
X-Rate-Remaining: 0
X-Rate-Reset: 3599

{  
    "error":"Request limit exceeded, please try again in 3599 seconds"
}
```

## Instructions

- You should fork this repository using your GitHub account and write your solution in your own branch and send to us within the agreed deadline.
- You should read the code base to understand what is already implemented and how you can use it to help you achieve the solution.
- You should try to design the most well designed and maintainable solution as possible, we will evaluate the implementation details and the design choices.
- You should not have to care about the runtime details like the dependency injection containers, database access layer, logger implementation or any other interface implementation, just trust in the available interfaces and feel free to create new ones. Remember, you're responsible to implement the business requirements, anything beyond that, is optional.
- Your code should be tested using unit tests and integration tests, feel free to use packages to mockup the expected behaviors.

(1) CEP stands by Código de Endereçamento Postal, that is the acronym for zipcode in Brazil.
