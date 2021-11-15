<?php

namespace App\Providers;

#----------------------------------- QUERIES --------------------------------
# Resolvers
use App\GraphQL\Queries\BusinessTripAttendanceResolver;
use App\GraphQL\Queries\BusinessTripScheduleResolver;
use App\GraphQL\Queries\SchoolTripAttendenceResolver;
use App\GraphQL\Queries\SchoolTripScheduleResolver;
use App\GraphQL\Queries\DocumentResolver;
use App\GraphQL\Queries\SeatsTripUserResolver;

# Interfaces
use App\Repository\EloquentRepositoryInterface; 
use App\Repository\Queries\MainRepositoryInterface;
use App\Repository\Queries\BusinessTripSubscriptionRepositoryInterface; 
use App\Repository\Queries\BusinessTripRepositoryInterface;
use App\Repository\Queries\CommunicationRepositoryInterface;
use App\Repository\Queries\SchoolCommunicationRepositoryInterface;
use App\Repository\Queries\NotificationRepositoryInterface;
use App\Repository\Queries\OndemandRequestRepositoryInterface;
use App\Repository\Queries\PartnerRepositoryInterface;
use App\Repository\Queries\PaymentCategoryRepositoryInterface;
use App\Repository\Queries\SchoolTripSubscriptionRepositoryInterface; 
use App\Repository\Queries\SchoolTripRepositoryInterface;
use App\Repository\Queries\SeatsLineStationRepositoryInterface;
use App\Repository\Queries\SeatsTripAppTransactionRepositoryInterface;
use App\Repository\Queries\SeatsTripBookingRepositoryInterface;
use App\Repository\Queries\SeatsTripRepositoryInterface;
use App\Repository\Queries\SeatsTripTerminalTransactionRepositoryInterface;
use App\Repository\Queries\SeatsTripPosTransactionRepositoryInterface;
use App\Repository\Queries\VehicleRepositoryInterface;

# Repositories
use App\Repository\Eloquent\BaseRepository; 
use App\Repository\Eloquent\Queries\BusinessTripAttendanceRepository; 
use App\Repository\Eloquent\Queries\BusinessTripSubscriptionRepository; 
use App\Repository\Eloquent\Queries\BusinessTripScheduleRepository; 
use App\Repository\Eloquent\Queries\BusinessTripRepository; 
use App\Repository\Eloquent\Queries\CommunicationRepository;
use App\Repository\Eloquent\Queries\SchoolCommunicationRepository;
use App\Repository\Eloquent\Queries\DocumentRepository;
use App\Repository\Eloquent\Queries\NotificationRepository;
use App\Repository\Eloquent\Queries\OndemandRequestRepository;
use App\Repository\Eloquent\Queries\PartnerRepository;
use App\Repository\Eloquent\Queries\PaymentCategoryRepository;
use App\Repository\Eloquent\Queries\SchoolTripAttendenceRepository;
use App\Repository\Eloquent\Queries\SchoolTripSubscriptionRepository;
use App\Repository\Eloquent\Queries\SchoolTripScheduleRepository;
use App\Repository\Eloquent\Queries\SchoolTripRepository;
use App\Repository\Eloquent\Queries\SeatsLineStationRepository;
use App\Repository\Eloquent\Queries\SeatsTripAppTransactionRepository;
use App\Repository\Eloquent\Queries\SeatsTripBookingRepository;
use App\Repository\Eloquent\Queries\SeatsTripRepository;
use App\Repository\Eloquent\Queries\SeatsTripTerminalTransactionRepository;
use App\Repository\Eloquent\Queries\SeatsTripPosTransactionRepository;
use App\Repository\Eloquent\Queries\SeatsTripUserRepository;
use App\Repository\Eloquent\Queries\VehicleRepository;

# ---------------------------------- MUTATIONS -----------------------------------
# Interfaces
use App\Repository\Mutations\BusinessTripEventRepositoryInterface;
use App\Repository\Mutations\BusinessTripRequestRepositoryInterface;
use App\Repository\Mutations\BusinessTripRepositoryInterface as BusinessTripRepoInterface;
use App\Repository\Mutations\BusinessTripScheduleRepositoryInterface;
use App\Repository\Mutations\BusinessTripStationRepositoryInterface;
use App\Repository\Mutations\CommunicationRepositoryInterface as CommunicationRepoInterface;
use App\Repository\Mutations\SchoolCommunicationRepositoryInterface as SchoolCommunicationRepoInterface;
use App\Repository\Mutations\DriverRepositoryInterface;
use App\Repository\Mutations\PartnerRepositoryInterface as PartnerRepoInterface;
use App\Repository\Mutations\PaymentRepositoryInterface;
use App\Repository\Mutations\PromoCodeRepositoryInterface;
use App\Repository\Mutations\SchoolTripEventRepositoryInterface;
use App\Repository\Mutations\SchoolTripRequestRepositoryInterface;
use App\Repository\Mutations\SchoolTripRepositoryInterface as SchoolTripRepoInterface;
use App\Repository\Mutations\SchoolTripScheduleRepositoryInterface;
use App\Repository\Mutations\SchoolTripStationRepositoryInterface;
use App\Repository\Mutations\SeatsLineRepositoryInterface;
use App\Repository\Mutations\SeatsTripEventRepositoryInterface;
use App\Repository\Mutations\UserRepositoryInterface;


# Repositories
use App\Repository\Eloquent\Mutations\BusinessTripEventRepository;
use App\Repository\Eloquent\Mutations\BusinessTripRequestRepository;
use App\Repository\Eloquent\Mutations\BusinessTripRepository as BusinessTripRepo;
use App\Repository\Eloquent\Mutations\BusinessTripScheduleRepository as BusinessTripScheduleRepo;
use App\Repository\Eloquent\Mutations\BusinessTripStationRepository;
use App\Repository\Eloquent\Mutations\CommunicationRepository as CommunicationRepo;
use App\Repository\Eloquent\Mutations\SchoolCommunicationRepository as SchoolCommunicationRepo;
use App\Repository\Eloquent\Mutations\DriverRepository;
use App\Repository\Eloquent\Mutations\PartnerRepository as PartnerRepo;
use App\Repository\Eloquent\Mutations\PaymentRepository;
use App\Repository\Eloquent\Mutations\PromoCodeRepository;
use App\Repository\Eloquent\Mutations\SchoolTripEventRepository;
use App\Repository\Eloquent\Mutations\SchoolTripRequestRepository;
use App\Repository\Eloquent\Mutations\SchoolTripRepository as SchoolTripRepo;
use App\Repository\Eloquent\Mutations\SchoolTripScheduleRepository as SchoolTripScheduleRepo;
use App\Repository\Eloquent\Mutations\SchoolTripStationRepository;
use App\Repository\Eloquent\Mutations\SeatsLineRepository;
use App\Repository\Eloquent\Mutations\SeatsTripEventRepository;
use App\Repository\Eloquent\Mutations\UserRepository;

use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        #----------------------------------- QUERIES --------------------------------
        
        $this->app->bind(EloquentRepositoryInterface::class, BaseRepository::class);
        $this->app->bind(BusinessTripSubscriptionRepositoryInterface::class, BusinessTripSubscriptionRepository::class);
        $this->app->bind(BusinessTripRepositoryInterface::class, BusinessTripRepository::class);
        $this->app->bind(CommunicationRepositoryInterface::class, CommunicationRepository::class);
        $this->app->bind(SchoolCommunicationRepositoryInterface::class, SchoolCommunicationRepository::class);
        $this->app->bind(NotificationRepositoryInterface::class, NotificationRepository::class);
        $this->app->bind(OndemandRequestRepositoryInterface::class, OndemandRequestRepository::class);
        $this->app->bind(PartnerRepositoryInterface::class, PartnerRepository::class);
        $this->app->bind(PaymentCategoryRepositoryInterface::class, PaymentCategoryRepository::class);
        $this->app->bind(SchoolTripSubscriptionRepositoryInterface::class, SchoolTripSubscriptionRepository::class);
        $this->app->bind(SchoolTripRepositoryInterface::class, SchoolTripRepository::class);
        $this->app->bind(SeatsLineStationRepositoryInterface::class, SeatsLineStationRepository::class);
        $this->app->bind(SeatsTripAppTransactionRepositoryInterface::class, SeatsTripAppTransactionRepository::class);
        $this->app->bind(SeatsTripBookingRepositoryInterface::class, SeatsTripBookingRepository::class);
        $this->app->bind(SeatsTripRepositoryInterface::class, SeatsTripRepository::class);
        $this->app->bind(SeatsTripTerminalTransactionRepositoryInterface::class, SeatsTripTerminalTransactionRepository::class);
        $this->app->bind(SeatsTripPosTransactionRepositoryInterface::class, SeatsTripPosTransactionRepository::class);
        $this->app->bind(VehicleRepositoryInterface::class, VehicleRepository::class);

        $this->app->when(BusinessTripAttendanceResolver::class)
                  ->needs(MainRepositoryInterface::class)
                  ->give(BusinessTripAttendanceRepository::class);

        $this->app->when(BusinessTripScheduleResolver::class)
                  ->needs(MainRepositoryInterface::class)
                  ->give(BusinessTripScheduleRepository::class);

        $this->app->when(SchoolTripAttendenceResolver::class)
                  ->needs(MainRepositoryInterface::class)
                  ->give(SchoolTripAttendenceRepository::class);

        $this->app->when(SchoolTripScheduleResolver::class)
                  ->needs(MainRepositoryInterface::class)
                  ->give(SchoolTripScheduleRepository::class);

        $this->app->when(DocumentResolver::class)
                  ->needs(MainRepositoryInterface::class)
                  ->give(DocumentRepository::class);
    
        $this->app->when(SeatsTripUserResolver::class)
                  ->needs(MainRepositoryInterface::class)
                  ->give(SeatsTripUserRepository::class);


        # ---------------------------------- MUTATIONS -----------------------------------

        $this->app->bind(BusinessTripEventRepositoryInterface::class, BusinessTripEventRepository::class);
        $this->app->bind(BusinessTripRequestRepositoryInterface::class, BusinessTripRequestRepository::class);
        $this->app->bind(BusinessTripRepoInterface::class, BusinessTripRepo::class);
        $this->app->bind(BusinessTripScheduleRepositoryInterface::class, BusinessTripScheduleRepo::class);
        $this->app->bind(BusinessTripStationRepositoryInterface::class, BusinessTripStationRepository::class);
        $this->app->bind(CommunicationRepoInterface::class, CommunicationRepo::class);
        $this->app->bind(SchoolCommunicationRepoInterface::class, SchoolCommunicationRepo::class);
        $this->app->bind(DriverRepositoryInterface::class, DriverRepository::class);
        $this->app->bind(PartnerRepoInterface::class, PartnerRepo::class);
        $this->app->bind(PaymentRepositoryInterface::class, PaymentRepository::class);
        $this->app->bind(PromoCodeRepositoryInterface::class, PromoCodeRepository::class);
        $this->app->bind(SchoolTripEventRepositoryInterface::class, SchoolTripEventRepository::class);
        $this->app->bind(SchoolTripRequestRepositoryInterface::class, SchoolTripRequestRepository::class);
        $this->app->bind(SchoolTripRepoInterface::class, SchoolTripRepo::class);
        $this->app->bind(SchoolTripScheduleRepositoryInterface::class, SchoolTripScheduleRepo::class);
        $this->app->bind(SchoolTripStationRepositoryInterface::class, SchoolTripStationRepository::class);
        $this->app->bind(SeatsLineRepositoryInterface::class, SeatsLineRepository::class);
        $this->app->bind(SeatsTripEventRepositoryInterface::class, SeatsTripEventRepository::class);
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);

    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
