import type { User } from './user.types'

export interface AuthLoginPayload {
  email: string
  password: string
}

export interface AuthRegisterPayload {
  name: string
  email: string
  password: string
  password_confirmation: string
  role: 'client' | 'freelancer'
}

export interface AuthLoginResponse {
  user: User
  token: string
}

export interface AuthRegisterResponse {
  user: User
  token: string
}
