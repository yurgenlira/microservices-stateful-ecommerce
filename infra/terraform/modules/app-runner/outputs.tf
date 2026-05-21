output "service_url" {
  value = aws_apprunner_service.this.service_url
}

output "service_arn" {
  value = aws_apprunner_service.this.arn
}

output "instance_role_arn" {
  value = aws_iam_role.instance.arn
}

output "ecr_access_role_arn" {
  value = aws_iam_role.ecr_access.arn
}