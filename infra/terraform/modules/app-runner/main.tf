resource "aws_iam_role" "ecr_access" {
  name = "${var.project_name}-app-runner-ecr"

  assume_role_policy = jsonencode({
    Version = "2012-10-17"
    Statement = [{
      Effect    = "Allow"
      Principal = { Service = "build.apprunner.amazonaws.com" }
      Action    = "sts:AssumeRole"
    }]
  })
}

resource "aws_iam_role_policy_attachment" "ecr_access" {
  role       = aws_iam_role.ecr_access.name
  policy_arn = "arn:aws:iam::aws:policy/service-role/AWSAppRunnerServicePolicyForECRAccess"
}

resource "aws_iam_role" "instance" {
  name = "${var.project_name}-app-runner-instance"

  assume_role_policy = jsonencode({
    Version = "2012-10-17"
    Statement = [{
      Effect    = "Allow"
      Principal = { Service = "tasks.apprunner.amazonaws.com" }
      Action    = "sts:AssumeRole"
    }]
  })
}

resource "aws_iam_role_policy" "ssm_read" {
  role = aws_iam_role.instance.id

  policy = jsonencode({
    Version = "2012-10-17"
    Statement = [{
      Effect   = "Allow"
      Action   = ["ssm:GetParameters", "ssm:GetParametersByPath"]
      Resource = "arn:aws:ssm:${var.aws_region}:*:parameter/${var.project_name}/${var.environment}/*"
    }]
  })
}

resource "aws_apprunner_vpc_connector" "this" {
  vpc_connector_name = "${var.project_name}-${var.environment}"
  subnets            = var.private_subnet_ids
  security_groups    = [var.app_security_group_id]
}

resource "aws_apprunner_auto_scaling_configuration_version" "this" {
  auto_scaling_configuration_name = "${var.project_name}-${var.environment}"
  max_concurrency                 = 25
  min_size                        = 1
  max_size                        = 10
}

resource "aws_apprunner_service" "this" {
  service_name = "${var.project_name}-${var.environment}"

  source_configuration {
    image_repository {
      image_configuration {
        port                          = "8080"
        runtime_environment_variables = var.environment_variables
        runtime_environment_secrets   = var.environment_secrets
      }
      image_identifier      = "${var.ecr_repository_url}:latest"
      image_repository_type = "ECR"
    }
    authentication_configuration {
      access_role_arn = aws_iam_role.ecr_access.arn
    }
    auto_deployments_enabled = false
  }

  instance_configuration {
    instance_role_arn = aws_iam_role.instance.arn
    cpu               = var.cpu
    memory            = var.memory
  }

  health_check_configuration {
    protocol            = "HTTP"
    path                = "/api/health"
    interval            = 10
    timeout             = 5
    healthy_threshold   = 1
    unhealthy_threshold = 3
  }

  auto_scaling_configuration_arn = aws_apprunner_auto_scaling_configuration_version.this.arn

  network_configuration {
    egress_configuration {
      egress_type       = "VPC"
      vpc_connector_arn = aws_apprunner_vpc_connector.this.arn
    }
  }
}
