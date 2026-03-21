export type UserRole = 'client' | 'freelancer'
export type Availability = 'AVAILABLE' | 'BUSY' | 'NOT_AVAILABLE'

export interface User {
  id: number
  name: string
  email: string
  email_verified_at: string | null
  role: UserRole
  avatar: string | null
  created_at: string
  updated_at: string
}

export interface ClientProfile {
  id: number
  user_id: number
  company_name: string | null
  company_field: string | null
  created_at: string
  updated_at: string
}

export interface FreelancerProfile {
  id: number
  user_id: number
  skills: string[]
  hourly_rate: number | null
  portfolio_url: string | null
  bio: string | null
  availability: Availability
  created_at: string
  updated_at: string
}

export interface UserWithProfile extends User {
  client_profile?: ClientProfile
  freelancer_profile?: FreelancerProfile
}
