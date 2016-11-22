Orchestration CloudFormation templates
---

Please create a CloudFormation template that satisfies the following requirements:
- Can be launched into any VPC with a public subnet
- Parameterized VPC, Subnet and bastion instance size
- Creates one bastion host, details below
- Create a NatGateway
- Write the template in yaml not json

bastion host
    * Name: Bastion<Region> ie. (BastionEast1)
    * Requirements
        - Can be used as a linux jump box
        - Can be used to proxy ssh and RDP connections to servers in any Subnet
        - Can be logged into from the internet
        - Runs on Ubuntu

To test this template make sure the following is possible
1. Spin up a linux server in a Private subnet and ssh to the server, download updates through the NAT
2. Spin up a linux server in a Public subnet and ssh to the server
3. Spin up a Windows server in a Private subnet and RDP to the server, download updates through the NAT
4. Spin up a Windows server in a Public subnet and RDP to the server
