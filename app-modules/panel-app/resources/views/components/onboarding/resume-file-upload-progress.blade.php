interface StatusStepProps {
label: string;
active: boolean;
completed: boolean;
icon: React.ReactNode;
}

const StatusStep: React.FC<StatusStepProps> = ({ label, active, completed, icon }) => (
    <div className="flex flex-col items-center gap-3">
        <div className={`
             w-10 h-10 rounded-full flex items-center justify-center transition-all duration-500 border
             ${completed ? 'bg-white text-black border-white shadow-[0_0_15px_rgba(255,255,255,0.3)]' :
        active ? 'bg-zinc-800 text-white border-zinc-600 shadow-lg scale-110' :
        'bg-zinc-900 text-zinc-700 border-zinc-800'}
        `}>
        {completed ? <CheckCircle2 className="w-5 h-5" /> : icon}
    </div>
    <span className={`text-[9px] font-black uppercase tracking-[0.2em] transition-colors duration-500 ${active ? 'text-white' : 'text-zinc-700'}`}>
    {label}
    </span>
    </div>
    );

    interface StatusStepperProps {
    status: ProcessingStatus;
    progress: number;
    }

    export const StatusStepper: React.FC<StatusStepperProps> = ({ status, progress }) => (
        <div className="space-y-6">
            <div className="flex justify-between items-center px-2">
                <StatusStep
                    label="Sent"
                    active={status !== ProcessingStatus.IDLE}
                    completed={status === ProcessingStatus.PROCESSING || status === ProcessingStatus.FINISHED}
                icon={<Send className="w-4 h-4" />}
                />
                <div className="h-0.5 flex-1 mx-4 bg-zinc-800 rounded-full">
                    <div className={`h-full bg-white transition-all duration-1000 ${status !== ProcessingStatus.IDLE ? 'w-full' : 'w-0'}`} />
                </div>
                <StatusStep
                    label="Processing"
                    active={status === ProcessingStatus.PROCESSING || status === ProcessingStatus.FINISHED}
                completed={status === ProcessingStatus.FINISHED}
                icon={<Loader2 className={`w-4 h-4 ${status === ProcessingStatus.PROCESSING ? 'animate-spin' : ''}`} />}
                />
                <div className="h-0.5 flex-1 mx-4 bg-zinc-800 rounded-full">
                    <div className={`h-full bg-white transition-all duration-1000 ${status === ProcessingStatus.PROCESSING || status === ProcessingStatus.FINISHED ? 'w-full' : 'w-0'}`} />
                </div>
                <StatusStep
                    label="Finished"
                    active={status === ProcessingStatus.FINISHED}
                completed={status === ProcessingStatus.FINISHED}
                icon={<CheckCircle2 className="w-4 h-4" />}
                />
            </div>

            <div className="relative h-2 w-full bg-zinc-800 rounded-full overflow-hidden shadow-inner border border-zinc-700">
                <div
                    className="absolute top-0 left-0 h-full bg-white transition-all duration-700 ease-out flex items-center justify-end overflow-hidden"
                    style={{ width: `${progress}%` }}
                >
                    <div className="progress-bar-shine absolute inset-0" />
                </div>
            </div>
        </div>
        );
