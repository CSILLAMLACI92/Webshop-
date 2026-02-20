const DEFAULT_AVATAR = "/uploads/Default.avatar.jpg";

u_pic.src =
  data.user.profile_pic && data.user.profile_pic.trim() !== ""
    ? data.user.profile_pic
    : DEFAULT_AVATAR;

u_pic.onerror = () => {
  u_pic.src = DEFAULT_AVATAR;
};


